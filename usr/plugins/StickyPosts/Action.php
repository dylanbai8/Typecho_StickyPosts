<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * StickyPosts 插件
 *
 * @package StickyPosts
 * @version 1.0
 * @author chatgpt
 * @link https://github.com/dylanbai8
 */

class StickyPosts_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        $indexFile = __TYPECHO_ROOT_DIR__ . '/usr/themes/' . Helper::options()->theme . '/index.php';
        $tag_txt = '<?php while ($this->next()): ?>'; // 锚点
        $add_txt = '<?php StickyPosts_Plugin::renderStickyPosts(); ?>';

        // 修改index.php
        if (file_exists($indexFile)) {
            $indexContent = file_get_contents($indexFile);
            if (strpos($indexContent, $add_txt) === false) {
                $indexContent = str_replace($tag_txt, "$add_txt$tag_txt", $indexContent);
                file_put_contents($indexFile, $indexContent);
            }
        }
    }

    public static function deactivate()
    {
        $indexFile = __TYPECHO_ROOT_DIR__ . '/usr/themes/' . Helper::options()->theme . '/index.php';
        $add_txt = '<?php StickyPosts_Plugin::renderStickyPosts(); ?>';

        // 还原index.php
        if (file_exists($indexFile)) {
            $indexContent = file_get_contents($indexFile);
            $indexContent = str_replace($add_txt, "", $indexContent);
            file_put_contents($indexFile, $indexContent);
        }
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $cids = new Typecho_Widget_Helper_Form_Element_Text(
            'cids',
            NULL,
            '',
            _t('置顶文章 CID'),
            _t('请填写要置顶文章的 CID，多个 CID 用英文逗号分隔')
        );
        $form->addInput($cids);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

public static function renderStickyPosts()
{
    $options = Helper::options();
    $get_cids = $options->plugin('StickyPosts')->cids;
    if ($get_cids) {$cids = explode(',', $get_cids);}

    if (!empty($cids)) {
        $db = Typecho_Db::get();
        $select = $db->select()
            ->from('table.contents')
            ->where('cid IN ?', $cids)
            ->where('status = ?', 'publish')
            ->where('type = ?', 'post')
            ->order('FIELD(cid, ' . implode(',', $cids) . ')');

        $posts = $db->fetchAll($select);

        if ($posts) {
            echo '<div>';
            foreach ($posts as $post) {
                echo '<article class="post">';
                echo '<h2>';
                echo '<a href="' . Typecho_Router::url('post', array('cid' => $post['cid']), $options->index) . '">[置顶] ' . $post['title'] . '</a>';
                echo '</h2>';
                echo '<div>' . mb_substr(strip_tags($post['text']), 0, 100) . ' ...</div>';
                echo '</article>';
            }
            echo '</div>';
        }
    }
}

}
?>
