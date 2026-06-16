# SpamBlocker - Typecho 垃圾评论拦截器

一个轻量级的 Typecho 插件，通过邮箱、域名、关键词黑名单自动拦截垃圾评论。

## 功能

- **邮箱拦截** — 支持部分匹配，如 `qq.com` 可拦截所有 QQ 邮箱
- **域名拦截** — 拦截评论者填写的网站域名
- **关键词拦截** — 拦截昵称或正文中的关键词
- **两种拦截方式**：
  - **直接拦截** — 匹配规则时拒绝评论，前台提示错误
  - **标记为垃圾** — 匹配规则时标记为 `spam` 状态，前台不报错也不显示，后台可审查

## 安装

1. 将 `SpamBlocker` 文件夹上传至 `/usr/plugins/`
2. 登录 Typecho 后台，进入 **控制台 → 插件**
3. 找到 **SpamBlocker**，点击 **启用**
4. 点击 **设置**，填写拦截规则
5. 详细图文教程见 [rongyan.cc/code/spamblocker.html](https://rongyan.cc/code/spamblocker.html)

## 使用方法

在插件设置页面填入需要拦截的内容，每行一条：

```
# 拦截邮箱（部分匹配）
spammer@example.com
qq.com

# 拦截域名（部分匹配）
spam-site.cn

# 拦截关键词
免费算命
```

选择拦截方式后保存即可生效。

## 数据存储

配置存储在 `typecho_options` 表中，`name = 'plugin:SpamBlocker'`，值为序列化数组。无需手动建表或改表结构。

## 拦截原理

通过 `Typecho_Plugin::factory('Widget_Feedback')->comment` 钩子，在评论提交前对邮箱、网址、昵称、正文进行匹配检查。

## 开源协议

MIT
