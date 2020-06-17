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
                'name' => ['高级助讲', '助教', '讲师', '副教授', '教授']
            ],
            [
                'type' => UserSearchConfig::TYPE_5,
                'name' => ['在职', '离职', '退休', '调离']
            ],
            [
                'type' => UserSearchConfig::TYPE_6,
                'name' => ['在编', '外聘兼职', '借调', '实习', '其他']
            ],
            [
                'type' => UserSearchConfig::TYPE_7,
                'name' => ['健康或良好', '一般或较弱', '有慢性病', '残疾']
            ],
            [
                'type' => UserSearchConfig::TYPE_8,
                'name' => ['农村', '县城', '乡镇非农', '城市']
            ],
            [
                'type' => UserSearchConfig::TYPE_9,
                'name' => ['统一招生考试/普通入学', '保送', '民族班', '定向培养', '体育特招', '文艺特招', '学生干部保送', '考试推荐', '外校转入', '恢复入学资格', '其他']
            ],
            [
                'type' => UserSearchConfig::TYPE_10,
                'name' => ['非分段培养', '中高职3+2', '五年一贯制', '中高职2+3', '中高职3+3', '中高职2.5+2.5', '中职本科3+4']
            ],
            [
                'type' => UserSearchConfig::TYPE_11,
                'name' => ['父亲', '母亲', '继父或养父', '继母或养母', '祖母', '外祖父', '外祖母', '兄弟', '姐妹', '其他亲属', '非亲属']
            ],
            [
                'type' => UserSearchConfig::TYPE_12,
                'name' => ['不困难', '一般困难', '困难', '特别困难']
            ],
            [
                'type' => UserSearchConfig::TYPE_13,
                'name' => ['三年制', '四年制', '五年制', '一年制', '一年半制', '两年制', '两年半制', '两年制', '三年半制', '四年半制', '五年半制', '六年制', '六年半制', '七年制', '八年制']
            ],
            [
                'type' => UserSearchConfig::TYPE_14,
                'name' => ['调整后中职学生', '职业高中学生', '普通中专学生', '成人中专学生', '技工学校学生']
            ],
            [
                'type' => UserSearchConfig::TYPE_15,
                'name' => ['应届初中毕业生', '应届高中毕业生', '往届初中毕业生', '进城务工人员', '农民', '退役军人', '城镇下岗职工', '高职高专学生', '本科及研究生学生', '其他']
            ],
            [
                'type' => UserSearchConfig::TYPE_16,
                'name' => ['非联合办学', '城乡联合办学', '东中西部联合办学']
            ],
            [
                'type' => UserSearchConfig::TYPE_17,
                'name' => ['统一招生', '自主招生', '其他']
            ],

        ];

        foreach ($arr as $value) {
            foreach ($value['name'] as $val) {
                UserSearchConfig::create(['name' => $val, 'type' => $value['type']]);
            }
        }
    }
}
