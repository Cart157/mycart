<?php

namespace Modules\Base\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

trait TreeViewTrait
{
    protected $tmp_tree_ids = [];
    protected $tmp_tree_arr = [];
    protected $tmp_tree_str = '';
    protected $tmp_parents_arr = [];

    public function getParents($item = null, $include_self = true)
    {
        $item = $item ?: $this;

        if ($include_self) {
            array_unshift($this->tmp_parents_arr, $item->id);
        }

        if ($item->parent) {
            $this->getParents($item->parent);
        }

        return $this->tmp_parents_arr;
    }

    public function scopeTreeIds()
    {
        $this->checkRelationship();
        return $this->getTreeIds();
    }

    // Builder $q 是scope默认注入的，调用时不用传
    public function scopeTreeArray(Builder $q, $column_name = 'name')
    {
        $this->checkRelationship();
        return $this->getTreeArray();
    }

    // Builder $q 是scope默认注入的，调用时不用传
    public function scopeTreeOptions(Builder $q, $column_name = 'name', $nullLable = '无父级')
    {
        $this->checkRelationship();

        $sorted_tree_ids = $this->getTreeIds();
        if (!empty($sorted_tree_ids)) {
            $ids_ordered = implode(',', $sorted_tree_ids);
            $option_list = $this->orderByRaw(\DB::raw("FIELD(id, $ids_ordered)"))->get();
        } else {
            $option_list = [];
        }

        $option_tree = [$nullLable];
        foreach ($option_list as $option) {
            $option_tree[$option->id] = $option->getPrefixColumnHtml($column_name);
        }

        return $option_tree;
    }

    protected function checkRelationship()
    {
        if (!method_exists($this, 'parent')) {
            throw new \Exception('缺少名为 parent() 的关系方法');
        }

        if (!method_exists($this, 'children')) {
            throw new \Exception('缺少名为 children() 的关系方法');
        }
    }

    protected function getTreeIds()
    {
        $this->tmp_tree_ids = [];

        $top_items = $this->where('parent_id', 0)->orderBy('sort_order')->get();
        $this->makeTreeSortIds($top_items);

        return $this->tmp_tree_ids;
    }

    protected function makeTreeSortIds($items, $depth = 0)
    {
        foreach ($items as $item) {
            $this->tmp_tree_ids[] = $item->id;

            if ($item->children->count() > 0) {
                $this->makeTreeSortIds($item->children, $depth + 1);
            }
        }
    }

    protected function getTreeArray($column_name = 'name')
    {
        // 转成多维数组
        $this->tmp_tree_arr = [];

        $top_items = $this->where('parent_id', 0)->get();
        $this->makeTreeArray($column_name, $top_items);

        return $this->tmp_tree_arr;
    }

    protected function makeTreeArray($column_name = 'name', $items, $depth = 0)
    {
        $children = [];
        foreach ($items as $item) {
            // 转成多维数组
            $arr_item = [
                'id' => $item->id,
                'text' => $item->{$column_name},
            ];

            if ($item->children->count() > 0) {
                $arr_item['children'] = $this->makeTreeArray($column_name, $item->children, $depth + 1);
            }

            if ($depth == 0) {
                $this->tmp_tree_arr[] = $arr_item;
            } else {
                $children[] = $arr_item;
            }
        }

        return $children;
    }

    // crud column
    public function getNameTreeHtml()
    {
        return $this->getPrefixColumnHtml();
    }

    protected function getPrefixColumnHtml($column_name = 'name', $depth = 0, $item = null)
    {
        $item = $item ?: $this;
        if ($depth == 0) {
            $this->tmp_tree_str = '　├　';
            if ($item->isLast()) {
                $this->tmp_tree_str = '　└　';
            }
        }

        if ($item->parent) {
            if ($item->parent->isLast()) {
                $this->tmp_tree_str = '　　　' . $this->tmp_tree_str;
            } else {
                $this->tmp_tree_str = '　│　' . $this->tmp_tree_str;
            }

            $this->getPrefixColumnHtml($column_name, $depth + 1, $item->parent);
        }

        return $this->tmp_tree_str . $this->{$column_name};
    }

    protected function isLast()
    {
        $last_node = $this->query()->where('parent_id', $this->parent_id)->orderBy('sort_order')->get()->last();
        return $last_node->id == $this->id;
    }
}
