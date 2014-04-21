<?php
/**
 * WeChatHelper Plugin
 *
 * @copyright  Copyright (c) 2013 Binjoo (http://binjoo.net)
 * @license    GNU General Public License 2.0
 * 
 */
include_once 'Utils.php';
class WeChatHelper_Widget_Menus extends Widget_Abstract implements Widget_Interface_Do {
    private $siteUrl, $_countSql, $_total = false;

    public function __construct($request, $response, $params = NULL) {
        parent::__construct($request, $response, $params);
        $this->siteUrl = Helper::options()->siteUrl;
    }

    public function select() {
        return $this->db->select()->from('table.wxh_menus');
    }
    public function insert(array $options) {
        return $this->db->query($this->db->insert('table.wxh_menus')->rows($options));
    }
    public function update(array $options, Typecho_Db_Query $condition){
        return $this->db->query($condition->update('table.wxh_menus')->rows($options));
    }
    public function delete(Typecho_Db_Query $condition){
        return $this->db->query($condition->delete('table.wxh_menus'));
    }
    public function size(Typecho_Db_Query $condition){
        return $this->db->fetchObject($condition->select(array('COUNT(table.wxh_menus.uid)' => 'num'))->from('table.wxh_menus'))->num;
    }

    public function execute(){
        /** 构建基础查询 */
        $select = $this->select()->from('table.wxh_menus');

        /** 给计算数目对象赋值,克隆对象 */
        $this->_countSql = clone $select;

        /** 提交查询 */
        $select->order('table.wxh_menus.order', Typecho_Db::SORT_ASC);
        $this->db->fetchAll($select, array($this, 'push'));
    }

    public function filter(array $value) {
        $value['levelVal'] = $value['level'] == 'button' ? '一级菜单' : '二级菜单';
        $value['tr'] = $value['level'] == 'button' ? 'background-color: #F0F0EC' : '';
        return $value;
    }

    public function push(array $value) {
        $value = $this->filter($value);
        return parent::push($value);
    }

    /**
     * 生成表单
     *
     * @access public
     * @param string $action 表单动作
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function form($action = NULL) {
        if (isset($this->request->mid) && 'insert' != $action) {
            /** 更新模式 */
            $menu = $this->db->fetchRow($this->select()->where('mid = ?', $this->request->mid)->limit(1));

            if (!$menu) {
                $this->response->redirect(Helper::url('WeChatHelper/Page/Menus.php', $this->options->adminUrl));
            }
        }
        /** 构建表格 */
        $form = new Typecho_Widget_Helper_Form($this->security->getIndex('action/WeChat?menus'), Typecho_Widget_Helper_Form::POST_METHOD);

        $select = $this->select()->where('table.wxh_menus.parent = ?', '0')->order('table.wxh_menus.order', Typecho_Db::SORT_ASC);
        $buttonMenus = $this->db->fetchAll($select);

        $parent = '<select name="parent">';
        foreach ($buttonMenus as $row) {
            $selected = '';
            if (isset($menu['parent']) && $menu['parent'] === $row['mid']) {
                $selected = ' selected="true"';
            }
            $parent .= '<option value="' . $row['mid'] . '"' . $selected . '>' . $row['name'] . '</option>';
        }
        $parent .= '</select>';

        $level = new Typecho_Widget_Helper_Form_Element_Radio('level', 
            array('button' => _t('一级菜单'), 'sub_button' => _t('二级菜单 '.$parent)),
            'button', _t('消息类型'), NULL);
        $form->addInput($level->multiMode());

        $name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL,
        _t('标题'), _t('菜单标题，不超过16个字节，子菜单不超过40个字节'));
        $form->addInput($name);

        $type = new Typecho_Widget_Helper_Form_Element_Radio('type', array('view' => _t('View类型'), 'click' => _t('Click类型')), 'view', _t('消息类型'), NULL);
        $form->addInput($type);

        $value = new Typecho_Widget_Helper_Form_Element_Text('value', NULL, NULL,
        _t('Key & URL值'), _t('View类型：菜单KEY值，用于消息接口推送，不超过128字节；<br />Click类型：网页链接，用户点击菜单可打开链接，不超过256字节。'));
        $form->addInput($value);

        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do', NULL, NULL);
        $form->addInput($do);

        $submit = new Typecho_Widget_Helper_Form_Element_Submit(NULL, NULL, NULL);
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if (isset($this->request->mid) && 'insert' != $action) {
            $level->value($menu['level']);
            $name->value($menu['name']);
            $type->value($menu['type']);
            $value->value($menu['value']);
            $submit->value(_t('编辑菜单'));
            $do->value('update');
        } else {
            $submit->value(_t('增加菜单'));
            $do->value('insert');
        }

        return $form;
    }

    /**
     * 分类排序
     *
     * @access public
     * @return void
     */
    public function orderMenu() {
        $menus = $this->request->filter('int')->getArray('mid');
        if ($menus) {
            //$this->sort($menus, 'menus');
            foreach ($menus as $sort => $mid) {
                $this->update(array('order' => $sort + 1),
                $this->db->sql()->where('mid = ?', $mid));
            }
        }

        if (!$this->request->isAjax()) {
            /** 转向原页 */
            $this->response->redirect(Typecho_Common::url('manage-categories.php', $this->options->adminUrl));
        } else {
            $this->response->throwJson(array('success' => 1, 'message' => _t('分类排序已经完成')));
        }
    }

    public function insertMenu() {
        //if ($this->form('insert')->validate()) {
        //    $this->response->goBack();
        //}
        /** 取出数据 */
        $menu = $this->request->from('level', 'name', 'type', 'value', 'parent');

        /** 插入数据 */
        $menu['mid'] = $this->db->query($this->insert($menu));
        $this->push($menu);

        $this->widget('Widget_Notice')->highlight('menus-mid-'.$menu['mid']);
        $this->widget('Widget_Notice')->set(_t('自定义回复 已经被增加'), 'success');
        $this->response->redirect(Helper::url('WeChatHelper/Page/Menus.php', $this->options->adminUrl));
    }

    public function updateMenu() {
        
    }

    public function action() {
        $this->security->protect();
        $this->on($this->request->is('do=insert'))->insertMenu();
        $this->on($this->request->is('do=update'))->updateMenu();
        $this->on($this->request->is('do=order'))->orderMenu();
        $this->response->redirect($this->options->adminUrl);
    }
}
