<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 垃圾评论拦截器
 *
 * @package SpamBlocker
 * @author rongyan
 * @version 1.0.0
 * @link https://rongyan.cc
 */
class SpamBlocker_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('SpamBlocker_Plugin', 'filterComment');
        return _t('插件已激活，请前往设置拦截规则');
    }

    public static function deactivate()
    {
        return _t('插件已禁用');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $action = new Typecho_Widget_Helper_Form_Element_Radio(
            'action',
            array(
                'reject' => _t('直接拦截（拒绝评论，前台报错）'),
                'spam'   => _t('标记为垃圾（前台不报错，后台可审查）'),
            ),
            'reject',
            _t('拦截方式'),
            _t('直接拦截：匹配的评论被拒绝并提示错误。<br>标记为垃圾：匹配的评论被标记为垃圾状态，前台不会显示。')
        );
        $form->addInput($action);

        $emails = new Typecho_Widget_Helper_Form_Element_Textarea(
            'emails',
            null,
            '5652455524@qq.com',
            _t('拦截邮箱（每行一个）'),
            _t('评论者的邮箱地址，支持部分匹配。<br>如输入 qq.com 可拦截所有 QQ 邮箱，输入 @example.com 可拦截指定域名邮箱。')
        );
        $form->addInput($emails);

        $domains = new Typecho_Widget_Helper_Form_Element_Textarea(
            'domains',
            null,
            "resobang.cn\ntiatiatoutiao.com",
            _t('拦截域名（每行一个）'),
            _t('评论者填写的网站地址中的域名，支持部分匹配。')
        );
        $form->addInput($domains);

        $keywords = new Typecho_Widget_Helper_Form_Element_Textarea(
            'keywords',
            null,
            "免费算命\n姓名测试打分",
            _t('拦截关键词（每行一个）'),
            _t('评论昵称或正文中包含的关键词。')
        );
        $form->addInput($keywords);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function filterComment($comment, $content)
    {
        $options = Helper::options()->plugin('SpamBlocker');
        $action = $options->action ?? 'reject';

        $mail = $comment['mail'] ?? '';
        $author = $comment['author'] ?? '';
        $url = $comment['url'] ?? '';
        $text = $comment['text'] ?? '';

        $matched = '';

        foreach (self::parseLines($options->emails ?? '') as $item) {
            if ($item !== '' && stripos($mail, $item) !== false) {
                $matched = '邮箱匹配：' . $item;
                break;
            }
        }

        if (!$matched) {
            foreach (self::parseLines($options->domains ?? '') as $item) {
                if ($item !== '' && stripos($url, $item) !== false) {
                    $matched = '域名匹配：' . $item;
                    break;
                }
            }
        }

        if (!$matched) {
            foreach (self::parseLines($options->keywords ?? '') as $item) {
                if ($item !== '' && (stripos($author, $item) !== false || stripos($text, $item) !== false)) {
                    $matched = '关键词匹配：' . $item;
                    break;
                }
            }
        }

        if ($matched) {
            if ($action === 'spam') {
                $comment['status'] = 'spam';
                return $comment;
            }
            throw new Typecho_Exception(_t('评论包含违规内容，已被拦截'));
        }

        return $comment;
    }

    private static function parseLines(string $text): array
    {
        return array_filter(preg_split('/\r\n|\r|\n/', $text), function ($v) {
            return trim($v) !== '';
        });
    }
}
