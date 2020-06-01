<?php

use Illuminate\Database\Seeder;
use App\Models\Users\UserSearchConfig;

class UserSearchConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            [
                'type' => UserSearchConfig::TYPE_0,
                'name' => ['汉族', '蒙古族', '回族', '藏族', '维吾尔族', '苗族', '彝族', '壮族', '布依族', '朝鲜族', '满族', '侗族', '瑶族', '土家族', '哈尼族', '哈萨克族',
                    '傣族', '黎族', '僳僳族', '佤族', '畲族', '高山族', '拉祜族', '水族', '东乡族', '纳西族', '景颇族', '柯尔克孜族', '土族', '达斡尔族', '仫佬族', '羌族',
                    '布朗族', '撒拉族', '毛南族', '仡佬族', '锡伯族', '阿昌族', '普米族', '塔吉克族', '怒族', '乌孜别克族', '俄罗斯族', '鄂温克族', '德昂族', '保安族',
                    '裕固族', '京族', '塔塔尔族', '独龙族', '鄂伦春族', '赫哲族', '门巴族', '珞巴族', '基诺族'
                ]
            ],
            [
                'type' => UserSearchConfig::TYPE_1,
                'name' => ['中共党员', '中共预备党员', '共青团员', '民革党员', '民盟盟员', '民建会员', '民进会员', '农工党党员', '致公党党员',
                '九三学社社员', '台盟盟员', '无党派人士', '群众']
            ],
            [
                'type' => UserSearchConfig::TYPE_2,
                'name' => ['小学', '初中', '高中', '中专',  '大专', '硕士研究生', '博士研究生']
            ],
            [
                'type' => UserSearchConfig::TYPE_3,
                'name' => ['学士', '硕士' ,'博士']
            ],
            [
                'type' => UserSearchConfig::TYPE_4,
                'name' => ['高级助讲','助教','讲师','副教授','教授']
            ],
            [
               'type' => UserSearchConfig::TYPE_5,
               'name' => ['在职', '离职', '退休', '调离']
            ],
            [
                'type' => UserSearchConfig::TYPE_6,
                'name' => ['在编', '外聘兼职', '借调', '实习', '其他']
            ],
        ];

        foreach ($arr as $value) {
            foreach ($value['name'] as $val) {
                UserSearchConfig::create(['name' => $val, 'type' => $value['type']]);
            }
        }
    }
}
