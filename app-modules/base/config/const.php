<?php

return [
    //不同等级考核允许的最大的错误条数
    'appr_examine_wrong_count' => [
        4,3,2,1
    ],
    //不同等级考核的数据条数
    'appr_examine_count' => [
        50,100,200,500
    ],
    //鉴定费用给鉴定师的比例
    'appr_scale' => 0.6,
    //鉴定鞋价值
    'appr_price' => 2000,
//    //鉴定费
//    'appr_cost' => 5,
//    //鉴定保价手续费率
//    'appr_goods_scale' => 0.02,
    //鉴定提交图片
    'appr_image_pos' => [
        'out'   => [
            'code'  => 'out',
            'name'  => '球鞋外观',
            'sort'  => 1,
        ],
        'mark'   => [
            'code'  => 'mark',
            'name'  => '鞋标',
            'sort'  => 2,
        ],
        'mb_line'   => [
            'code'  => 'mb_line',
            'name'  => '中底走线',
            'sort'  => 3,
        ],
        'insole_mark'   => [
            'code'  => 'insole_mark',
            'name'  => '鞋垫正面',
            'sort'  => 4,
        ],
        'insole_glue'   => [
            'code'  => 'insole_glue',
            'name'  => '鞋垫反面',
            'sort'  => 5,
        ],
        'outsoles'   => [
            'code'  => 'outsoles',
            'name'  => '鞋底',
            'sort'  => 6,
        ],
        'box_stamp'   => [
            'code'  => 'box_stamp',
            'name'  => '鞋盒钢印',
            'sort'  => 7,
        ],
        'box_mark'   => [
            'code'  => 'box_mark',
            'name'  => '鞋盒侧标',
            'sort'  => 8,
        ],
        'box_cert'   => [
            'code'  => 'box_cert',
            'name'  => '鞋盒背面合格证',
            'sort'  => 9,
        ],
        'filler'   => [
            'code'  => 'filler',
            'name'  => '球鞋鞋撑',
            'sort'  => 10,
        ],
        'toe_cap'   => [
            'code'  => 'toe_cap',
            'name'  => '鞋头部',
            'sort'  => 11,
        ],
        'heelpiece'   => [
            'code'  => 'heelpiece',
            'name'  => '鞋后跟',
            'sort'  => 12,
        ],
    ],
    'coin_use_way' => [
        '1'   => [
            'code'  => '1',
            'name'  => '兑换优惠券/物品',
        ],
        '2'   => [
            'code'  => '2',
            'name'  => '金币抽奖消耗',
        ],
        '3'   => [
            'code'  => '3',
            'name'  => '鉴定',
        ],
        '4'   => [
            'code'  => '4',
            'name'  => '选秀活动投票',
        ],
        '5'   => [
            'code'  => '5',
            'name'  => '商城抵价',
        ],
    ],
    'coin_get_way' => [
        '1'   => [
            'code'  => '1',
            'name'  => '签到',
        ],
        '2'   => [
            'code'  => '2',
            'name'  => '邀请好友',
        ],
        '3'   => [
            'code'  => '3',
            'name'  => '点赞/评论',
        ],
        '4'   => [
            'code'  => '4',
            'name'  => '发表动态',
        ],
        '5'   => [
            'code'  => '5',
            'name'  => '分享',
        ],
        '6'   => [
            'code'  => '6',
            'name'  => '金币抽奖获得',
        ],
        '7'   => [
            'code'  => '7',
            'name'  => '系统赠送',
        ],
        '8'   => [
            'code'  => '8',
            'name'  => '系统补偿',
        ],
        '9'   => [
            'code'  => '9',
            'name'  => '手动调整',
        ],
        '10'   => [
            'code'  => '10',
            'name'  => '拼团奖励',
        ],
    ],

    'logistics_express' => [
        'yd_baoyou' => [
            'code'  => 'yd_baoyou',
            'name'  => '韵达包邮',
            'fee'   => 0,
            'insurance_rate' => '0.005',
        ],
        'yd_sudi' => [
            'code'  => 'yd_sudi',
            'name'  => '韵达速递',
            'fee'   => 12,
            'insurance_rate' => '0.005',
        ],
        'sf_daofu' => [
            'code'  => 'sf_daofu',
            'name'  => '顺丰到付',
            'fee'   => 0,
            'insurance_rate' => '0.005',
        ],
        'sf_tehui' => [
            'code'  => 'sf_tehui',
            'name'  => '顺丰特惠',
            'fee'   => 23,
            'insurance_rate' => '0.005',
        ],
        'sf_kongyun' => [
            'code'  => 'sf_kongyun',
            'name'  => '顺丰空运',
            'fee'   => 33,
            'insurance_rate' => '0.005',
        ],
    ],

    'android_activity' => [
        'moment-comment'        => 'com.wj.shoes.ui.activity.my.trend_info.TrendInfoActivity',
        'moment-comment-reply'  => 'com.wj.shoes.ui.activity.my.comment.CommentActivity',
        'article-comment'       => 'com.wj.shoes.ui.activity.main.article.ArticleActivity',
        'article-comment-reply' => 'com.wj.shoes.ui.activity.main.article_comment.ArticleCommentActivity',
        'item-comment-reply'    => 'com.wj.shoes.ui.activity.home.shoes.sku_comment_second.SkuCommentSecondActivity',
        'ext-activity-draw'     => 'com.wj.shoes.ui.activity.my.activities_info.info_draw.InfoDrawActivity',
        'ext-activity-start'    => 'com.wj.shoes.ui.activity.my.activities_info.info_show.InfoShowActivity',
        'ext-activity-vote'     => 'com.wj.shoes.ui.activity.my.activities_info.info_show.InfoShowActivity',
        'appraisal'             => 'com.wj.shoes.ui.activity.main.appraise_info.AppraiseInfoActivity',
        'special-notification'  => 'com.wj.shoes.ui.activity.message.system_notice.SysNoticeActivity',
    ],
];
