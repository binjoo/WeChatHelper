<?php
/**
 * 让你的微信公众帐号和Typecho博客联系起来
 * 
 * @package WeChatHelper
 * @author 冰剑
 * @version 1.1.0
 * @link http://www.binjoo.net
 */
class WeChatHelper_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Helper::addAction('wechatHelper', 'WeChatHelper_Action');
        Helper::addRoute('wechat', '/wechat', 'WeChatHelper_Action', 'link');
        //Helper::addPanel(1, 'WeChatHelper/Panel.php', '微信助手', '微信助手设置', 'administrator');
        return('微信助手已经成功激活，请进入设置Token!');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        Helper::removeRoute('wechat');
        Helper::removeAction('wechatHelper');
        //Helper::removePanel(1, 'WeChatHelper/Panel.php');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {


        /** Token **/
        $token = new Typecho_Widget_Helper_Form_Element_Text('token', NULL, '845C2550903CE6FA54CACDB82EAD4350', _t('微信Token'), '可以任意填写，用作生成签名。');
        $form->addInput($token);
        /** 用户添加订阅欢迎语 **/
        $welcome = new Typecho_Widget_Helper_Form_Element_Textarea('welcome', NULL, '哟，客官，您来啦！'.chr(10).'发送\'h\'让小的给您介绍一下！', '订阅欢迎语', '用户订阅之后主动发送的一条欢迎语消息。');
        $form->addInput($welcome);
        /** 返回最大结果条数 **/
        $imageDefault = new Typecho_Widget_Helper_Form_Element_Text('imageDefault', NULL, 'http://s0.binjoo.net/201305/494/36081_m.png', _t('默认显示图片'), '图片链接，支持JPG、PNG格式，推荐图为80*80。');
        $form->addInput($imageDefault);
        /** 返回最大结果条数 **/
        $imageNum = new Typecho_Widget_Helper_Form_Element_Text('imageNum', NULL, '5', _t('返回图文数量'), '图文消息数量，限制为10条以内。');
        $imageNum->input->setAttribute('class', 'mini');
        $form->addInput($imageNum);
        /** 水墙 TOP 排行榜显示数量 **/
        $rankNum = new Typecho_Widget_Helper_Form_Element_Text('rankNum', NULL, '10', _t('访客评论排行榜'), '显示的排行榜数量。');
        $rankNum->input->setAttribute('class', 'mini');
        $form->addInput($rankNum);
        /** 日志截取字数 **/
        $subMaxNum = new Typecho_Widget_Helper_Form_Element_Text('subMaxNum', NULL, '200', _t('日志截取字数'), '显示单条日志时，截取日志内容字数。');
        $subMaxNum->input->setAttribute('class', 'mini');
        $form->addInput($subMaxNum);
        /** 绑定校验字符串 start **/
        $_userNo = "";
        $_desc = "";
        $_setKey = "";
        $_setVal = "";
        try {   
            $_userNo = Helper::options()->plugin('WeChatHelper')->bindUserNo; 
        } catch (Exception $e) {
        } 
        if($_userNo == null || $_userNo == ""){
            $_desc = "【未绑定】";
            $_setKey = "class";
            $_setVal = "mini";
        }else{
            $_desc = "【<span style=\"color: #BD6800\">已绑定</span>】";
            $_setKey = "style";
            $_setVal = "display:none";
        }
        $bindCaptcha = new Typecho_Widget_Helper_Form_Element_Text('bindCaptcha', NULL, WeChatHelper_Plugin::randString(8), _t('绑定微信用户'), $_desc."激活插件时随机生成验证码，与微信用户绑定后，可以使用更多的功能。");
        $bindCaptcha->input->setAttribute($_setKey, $_setVal);
        $form->addInput($bindCaptcha);
        $bindUserNo = new Typecho_Widget_Helper_Form_Element_Hidden('bindUserNo', NULL, NULL, _t('帐号绑定'), NULL);
        $form->addInput($bindUserNo);
        /** 绑定校验字符串 end **/
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 生成随机字符串
     *
     * @access public
     * @param integer $length 字符串长度
     * @param string $specialChars 是否有特殊字符
     * @return string
     */
    public function randString($length)
    {
        $result = "";
        $chars = 'ACDEFGHJKMNPQRSTUVWXYZ2345679';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[rand(0, $max)];
        }
        return $result;
    }
}
