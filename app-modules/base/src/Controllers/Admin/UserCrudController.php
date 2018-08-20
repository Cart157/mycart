<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;
use Modules\Base\Models;
use Modules\Appraisal\Models as ApprModels;
use Modules\Cms\Models as CmsModels;
use Validator;
use Illuminate\Validation\Rule;
use GuzzleHttp;

class UserCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel("Modules\Base\Models\User");
        $this->crud->setEntityNameStrings('用户', '用户');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/user');

        // Columns.
        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
                'type'  => 'text',
            ],
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
            ],
            [ // n-n relationship (with pivot table)
               'label'     => '角色', // Table column heading
               'type'      => 'select_multiple',
               'name'      => 'roles', // the method that defines the relationship in your Model
               'entity'    => 'roles', // the method that defines the relationship in your Model
               'attribute' => 'name', // foreign key attribute that is shown to user
               // 'model'     => "Modules\Base\Models\Role", // foreign key model
            ],
            [
                'name'     => 'alipay_account',
                'label'    => '支付宝账户',
                'type'     => 'select',
                'entity'   => 'profile',
                'attribute'=> 'alipay_account',

            ],
            [
                'name'     => 'alipay_realname',
                'label'    => '真实姓名',
                'type'     => 'select',
                'entity'   => 'profile',
                'attribute'=> 'alipay_realname',

            ]
        ]);
    }

    public function index()
    {
        if (Request::has('wd')) {
            $this->crud->addClause('whereRaw', "CONCAT(IFNULL(base_user.name,''),IFNULL(base_user_profile.mobile,'')) like ?", ['%'.Request::input('wd').'%']);
            $this->crud->addClause('leftJoin', 'base_user_profile', 'base_user.id', '=', 'base_user_profile.user_id');
        }

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        $this->crud->addField([
            'name'      => 'name',
            'label'     => trans('backpack::permissionmanager.name'),
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'email',
            'label'     => trans('backpack::permissionmanager.email'),
            'type'      => 'email',
        ]);
        $this->crud->addField([
            'name'      => 'avatar',
            'label'     => '头像',
            'type'      => 'upload',
            'upload'    => true,
        ]);
        $this->crud->addField([
            'name'      => 'mobile',
            'label'     => '手机号',
            'type'      => 'number',
        ]);
        $this->crud->addField([
            'name'      => 'password',
            'label'     => '密码',
            'type'      => 'password',
        ]);
        $this->crud->addField([
            'name'      => 'password_confirmation',
            'label'     => '密码确认',
            'type'      => 'password',
        ]);

        $this->crud->addField([
            'name' => 'separator_rbac',
            'type' => 'custom_html',
            'value' => '<br><br>++++++++++++++++++++++++++++++++++++++++ rbac 权限设置 ++++++++++++++++++++++++++++++++++++++++<br><br></span>',
        ]);

        return view($this->crud->getCreateView(), $this->data);
    }

    /**
     * Store a newly created resource in the database.
     *
     * @param StoreRequest $request - type injection used for validation using Requests
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $validator = Validator::make(Request::all(),[
            'name'                  => 'required',
            'mobile'                => 'required|unique:base_user_profile,mobile|digits:11',
            'email'                 => [
                Rule::unique('base_user')->where(function ($query) {
                    $query->whereNotNull('email');
                }),
            ],
            'password_confirmation' => 'required',
            'password'              => 'required|min:6|max:16|confirmed',
            'rbac_role'             => 'integer',
        ]);

        $this->validateWith($validator);

        Request::merge(['password' => bcrypt(Request::input(['password']))]);
        $crud_rs = parent::storeCrud();
        $user = $this->data['entry'];

        if (isset(Request::all()['avatar'])) {
            $path_parts = pathinfo(Request::file('avatar')->getClientOriginalName());
            $path = Request::file('avatar')->storeAs(sprintf('base/user/%d', $user->id), 'avatar.' . $path_parts['extension'], 'uploads');
            $avatar_path = '/uploads/'.$path;
        } else {
            $avatar_path = null;
        }

        $userProfile = new Models\UserProfile;
        $userProfile->user_id = $user->id;
        $userProfile->mobile = Request::input('mobile');
        $userProfile->avatar = $avatar_path;
        $userProfile->save();

        // 3.创建网易云IM帐号
        $guzzle = new GuzzleHttp\Client;

        $accid      = Models\UserProfile::makeImUser();//'bu-'. sprintf('%010s', $user->id);
        $nonce      = mt_rand(100000, 999999);
        $cur_time   = time();
        $check_sum  = sha1('2144fd0f6416' . $nonce . $cur_time);

        $response = $guzzle->post('https://api.netease.im/nimserver/user/create.action', [
            'body'    => 'accid='. $accid,
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded;charset=utf-8',
                'AppKey'        => 'd988edda82c87e01723014b7df8b031b',
                'Nonce'         => $nonce,
                'CurTime'       => $cur_time,
                'CheckSum'      => $check_sum,
            ],
        ]);

        $netease_res = json_decode((string) $response->getBody(), true);

        if ($netease_res['code'] == '200') {
            $user_profile = Models\UserProfile::find($user->id);
            $user_profile->im_user  = $accid;
            $user_profile->im_token = $netease_res['info']['token'];
            $user_profile->save();
        }

        if (Request::input('rbac_role') > 0) {
            $user->roles()->sync([Request::input('rbac_role')]);

            if ($user->roleIs('appraiser')) {
                ApprModels\Appraiser::create([
                    'user_id'   => $user->id,
                    'bio'       => '知名鉴定师',
                ]);
            };

            if ($user->roleIs('author')) {
                CmsModels\Author::create([
                    'user_id'   => $user->id,
                    'real_name' => $user->name,
                ]);
            }
        }

        return $crud_rs;
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);

        $this->crud->addField([
            'name'      => 'name',
            'label'     => trans('backpack::permissionmanager.name'),
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'email',
            'label'     => trans('backpack::permissionmanager.email'),
            'type'      => 'email',
        ]);
        $this->crud->addField([
            'name'      => 'avatar',
            'label'     => '头像',
            'type'      => 'upload',
            'upload'    => true,
            'value'     => $this->data['entry']->profile->avatar,
        ]);
        $this->crud->addField([
            'name'      => 'mobile',
            'label'     => '手机号',
            'type'      => 'number',
            'value'     => $this->data['entry']->profile->mobile,
        ]);
        $this->crud->addField([
            'name'      => 'password',
            'label'     => '密码',
            'type'      => 'password',
        ]);
        $this->crud->addField([
            'name'      => 'password_confirmation',
            'label'     => '密码确认',
            'type'      => 'password',
        ]);

        $this->crud->addField([
            'name' => 'separator_rbac',
            'type' => 'custom_html',
            'value' => '<br><br>++++++++++++++++++++++++++++++++++++++++ rbac 权限设置 ++++++++++++++++++++++++++++++++++++++++<br><br></span>',
        ]);

        // $role_tree_ids = (new Models\RbacRole)->getRoleTree();
        // if (!empty($role_tree_ids)) {
        //     $ids_ordered = implode(',', $role_tree_ids);
        //     $role_list = Models\RbacRole::orderByRaw(\DB::raw("FIELD(id, $ids_ordered)"))->get();
        // } else {
        //     $role_list = [];
        // }

        // $role_tree = ['无父级'];
        // foreach ($role_list as $role) {
        //     $role_tree[$role->id] = $role->getNameHtml();
        // }
        // $this->crud->addField([
        //     'name'       => 'rbac_role',
        //     'label'      => '角色',
        //     'type'       => 'select2_from_array',
        //     'options'    => $role_tree,
        // ]);
        // $this->crud->addField([
        //     'name'       => 'rbac_role',
        //     'label'      => '角色',
        //     'type'       => 'select2_from_array',
        //     'options'    => Models\RbacRole::treeOptions($column_name = 'name', $nullLable = '请选择'),
        //     'value'      => $this->data['entry']->roles->first() ? $this->data['entry']->roles->first()->id : 0,
        //     'wrapperAttributes'     => [
        //         'class'             => 'form-group col-md-6',
        //     ],
        // ]);
        $this->crud->addField([
            'name'       => 'rbac_role',
            'label'      => '角色',
            'type'       => 'select2_multiple',
            'attribute'  => 'name',
            'model'      => 'Modules\Base\Models\RbacRole',
            'value'      => $this->data['entry']->roles,
            'wrapperAttributes'     => [
                'class'             => 'form-group col-md-6',
            ],
        ]);

        $role_options = Models\RbacRole::treeOptions($column_name = 'name', $nullLable = '角色层级参考（可复制后搜索）');
        $role_text = implode("\n", $role_options);
        $this->crud->addField([
            'name' => 'rbac_role_category_desc',
            'type' => 'custom_html',
            'value' => '<pre>' . $role_text . '</pre>',
            'wrapperAttributes'     => [
                'class'             => 'form-group col-md-9',
            ],
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['title'] = trans('base::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * Update the specified resource in the database.
     *
     * @param UpdateRequest $request - type injection used for validation using Requests
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        $validator = Validator::make(Request::all(),[
            'name'                  => 'required',
            'mobile'                => [
                // Rule::unique('base_user_profile')->ignore(Request::input('id'), 'user_id'),
                'digits:11',
            ],
            'email'                 => [
                Rule::unique('base_user')->where(function ($query) {
                    $query->whereNotNull('email')->where('id', '<>', Request::input('id'));
                }),
            ],
            'password'              => 'nullable|min:6|max:16|confirmed',
            'rbac_role'             => 'nullable|array',
            'rbac_role.*'           => 'integer',
        ]);

        $this->validateWith($validator);

        if (Request::has('password')) {
            Request::merge(['password' => bcrypt(Request::input(['password']))]);
        } else {
            // 你妈，这个方法找死我了
            Request::offsetUnset('password');
        }

        $crud_rs = parent::updateCrud();
        $user = $this->data['entry'];
        $userProfile = Models\UserProfile::find($user->id);

        if (isset(Request::all()['avatar'])) {
            $path_parts = pathinfo(Request::file('avatar')->getClientOriginalName());
            $path = Request::file('avatar')->storeAs(sprintf('base/user/%d', $user->id), 'avatar.' . $path_parts['extension'], 'uploads');
            $avatar_path = '/uploads/'.$path;
        }elseif ($userProfile->avatar) {
            $avatar_path = $userProfile->avatar;
        } else {
            $avatar_path = null;
        }

        $userProfile->mobile = Request::input('mobile') ?: $userProfile->mobile;
        $userProfile->avatar = $avatar_path;
        $userProfile->save();

        $user_info = [
            'accid' => $userProfile->im_user,//'bu-'. sprintf('%010s', $user->id),
        ];

        if (Request::has('name')) {
            $user_info['name'] = Request::input('name');
        }

        if (Request::has('avatar')) {
            $user_info['icon'] = cdn(). $avatar_path;
        }

        $response = call_netease('https://api.netease.im/nimserver/user/updateUinfo.action', $user_info);

        if (!Request::has('rbac_role')) {
            Request::merge(['rbac_role' => []]);
        }

        $old_role = $user->roles()->get()->pluck('slug')->all();
        $user->roles()->sync(Request::input('rbac_role'));
        $new_role = $user->roles()->get()->pluck('slug')->all();

        // 新加和删除的角色
        $add = array_diff($new_role, $old_role);
        $del = array_diff($old_role, $new_role);

        foreach ($add as $slug) {
            if ($slug == 'appraiser') {
                $appraiser = ApprModels\Appraiser::withTrashed()->where('user_id', $user->id)->first();

                if ($appraiser) {
                    $appraiser->restore();
                } else {
                    ApprModels\Appraiser::create([
                        'user_id'   => $user->id,
                        'bio'       => '知名鉴定师',
                    ]);
                }
            } elseif ($slug == 'author') {
                $author = CmsModels\Author::withTrashed()->where('user_id', $user->id)->first();

                if ($author) {
                    $author->restore();
                } else {
                    CmsModels\Author::create([
                        'user_id'   => $user->id,
                        'real_name' => $user->name,
                    ]);
                }
            }
        }

        // 删除了特定角色
        foreach ($del as $slug) {
            if ($slug == 'appraiser') {
                ApprModels\Appraiser::where('user_id', $user->id)->delete();
            } elseif ($slug == 'author') {
                CmsModels\Author::where('user_id', $user->id)->delete();
            }
        }
        // } else {
        //     // 删除所有角色
        //     \DB::table('base_rbac_user_role_mst')->where('user_id', $user->id)->delete();

        //     ApprModels\Appraiser::where('user_id', $user->id)->delete();
        //     CmsModels\Author::where('user_id', $user->id)->delete();
        //     // TODO:删除定制师，洗护师
        // }
        // // 警告：这里调了 $user->roles 导致 $user->roleIs() 这里判断的是老身份
        // $old_role = $user->roles->first() ? $user->roles->first()->slug : '';

        // if (Request::input('rbac_role') > 0) {
        //     $user->roles()->sync([Request::input('rbac_role')]);

        //     $new_role = Models\RbacRole::find(Request::input('rbac_role'))->slug;

        //     if ($new_role == 'appraiser' && $old_role != 'appraiser') {
        //         $appraiser = ApprModels\Appraiser::withTrashed()->where('user_id', $user->id)->first();
        //         if ($appraiser) {
        //             $appraiser->restore();
        //         } else {
        //             ApprModels\Appraiser::create([
        //                 'user_id'   => $user->id,
        //                 'bio'       => '知名鉴定师',
        //             ]);
        //         }

        //         CmsModels\Author::where('user_id', $user->id)->delete();
        //     };

        //     if ($new_role == 'author' && $old_role != 'author') {
        //         $author = CmsModels\Author::withTrashed()->where('user_id', $user->id)->first();

        //         if ($author) {
        //             $author->restore();
        //         } else {
        //             CmsModels\Author::create([
        //                 'user_id'   => $user->id,
        //                 'real_name' => $user->name,
        //             ]);
        //         }

        //         ApprModels\Appraiser::where('user_id', $user->id)->delete();
        //     }
        // } else {
        //     \DB::table('base_rbac_user_role_mst')->where('user_id', $user->id)->delete();
        //     ApprModels\Appraiser::where('user_id', $user->id)->delete();
        //     CmsModels\Author::where('user_id', $user->id)->delete();
        // }

        return $crud_rs;
    }
}
