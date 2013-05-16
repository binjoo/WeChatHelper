<?php
/**
 * 微信助手
 * 
 * @package WeChatHelper
 * @author binjoo
 * @version 1.0.0
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
        /** 返回最大结果条数 **/
        $imageNum = new Typecho_Widget_Helper_Form_Element_Text('imageNum', NULL, '5', _t('返回图文数量'), '图文消息数量，限制为10条以内。');
        $form->addInput($imageNum);
        /** 水墙 TOP 排行榜显示数量 **/
        $rankNum = new Typecho_Widget_Helper_Form_Element_Text('rankNum', NULL, '10', _t('访客评论排行榜'), '显示的排行榜数量。');
        $form->addInput($rankNum);
        /** 水墙 TOP 排行榜显示数量 **/
        $subMaxNum = new Typecho_Widget_Helper_Form_Element_Text('subMaxNum', NULL, '200', _t('日志截取字数'), '显示单条日志时，截取日志内容字数。');
        $form->addInput($subMaxNum);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
