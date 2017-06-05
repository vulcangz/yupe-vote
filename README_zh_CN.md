# yupe-vote

这个模块允许为你的模型附加投票、喜欢或者收藏按钮的小工具。(移植自 [hauntd/yii2-vote](https://github.com/hauntd/yii2-vote)).

![Demo](https://github.com/vulcangz/yupe-vote/raw/master/docs/screenshot.gif)

- 将尽可能多的小工具附加到模型中；
- 有用的小工具包括（收藏按钮，喜欢按钮，投票“向上/向下”）

## 依赖

1. Yupe - 基于Yii框架1.x 版本的CMS (已在yupe v1.1上测试)

## 安装

### 1、下载并拷贝“vote”文件夹到“protected/modules/”

### 2、配置应用

2.1 配置模块

编辑模块设置文件 (模板文件: vote/install/vote.php), 然后把它拷贝到 “protected/config/modules/”

实体名字必须以驼峰式命名，如 `itemVote`, `itemVoteGuests`, `itemLike` 和 `itemFavorite`.

```php
return [
  'modules' => [
    'vote' => [
      'class' => 'application.modules.vote.VoteModule',
        'guestTimeLimit' => 3600,  // 访客投票时间间隔，默认3600秒
        'entities' => [
          // 第一个实体设置
		  // Blog post Entity -> Settings
		  'postVote' => 'post',  // 你的模型，这里为'post'
		  'postVoteGuests' => [
			  'modelName' => 'post',  // 你的模型，这里为'post'
			  'allowGuests' => true,  // 是否允许访客投票？
		  ],
		  'postLike' => [
			  'modelName' => 'post',  // 你的模型，这里为'post'
			  'type' => 'toggle',  // TYPE_TOGGLE like/favorite 切换按钮
		  ],
		  'postFavorite' => [
			  'modelName' => 'post',  // 你的模型，这里为'post'
			  'type' => 'toggle',  // like/favorite 切换按钮
		  ],
		  // 下面你可以为别的模型添加实体
		  /*
          'itemVote' => 'product',  // 你的模型, 如 'product', 'post'
          'itemVoteGuests' => [
              'modelName' => 'product',  // 你的模型
              'allowGuests' => true,
          ],
          'itemLike' => [
              'modelName' => 'product',  // 你的模型
              'type' => 'toggle', // like/favorite 切换按钮
          ],
          'itemFavorite' => [
              'modelName' => 'product',  // 你的模型
              'type' => 'toggle', // like/favorite 切换按钮
          ], */
      ],
    ],
  ],
  'components' => [
    ...
  ]
];
```

2.2 为你的模型添加‘behavior’（行为）
例如：为‘blog’模块中的‘post’模型添加‘behavior’，编辑 protected/modules/blog/models/Post.php
```php
public function behaviors()
{
	$module = Yii::app()->getModule('blog');

	return [
		//...			
		'vote' => [
			'class' => 'application.modules.vote.components.behaviors.VoteBehavior',
			'entity' => 'postVote',	 // 必须与上一步中设置的实体名字相同
		],
	];
}
```

2.3 为你的视图增加投票小工具
例如：为“blog/post/view”的视图添加所有的三个小工具 (对应文件：themes/default/views/blog/post/view.php)

```
<div class="row like_box">
	<div class="col-sm-12">
		<div class="pull-left">
			<?php $this->widget('application.modules.vote.widgets.LikeWidget', [
				'entity' => 'postLike',  // 之前定义的实体名称
				'model' => $post,  // 你的模型
			]); ?>
		</div>
		<div class="col-sm-4 hidden-xs">
			<?php $this->widget('application.modules.vote.widgets.FavoriteWidget', [
				'entity' => 'postFavorite',
				'model' => $post,
			]); ?>
		</div>
		<div class="pull-right">					
			<?php $this->widget('application.modules.vote.widgets.VoteWidget', [
				'entity' => 'postVote',
				'model' => $post,
				'options' => ['class' => 'vote vote-visible-buttons'],
			]); ?>
		</div>
	</div>
</div>
```

### 3、更新数据库模式
下载并配置好yupe-vote之后, 下来必须要做的是通过应用迁移来更新数据库模式:

3.1 方式一: 通过"Yupe!" CMS的控制面板

登录进入yupe后台，在首页 - "Fast access to modules"（快速访问模块）, 点击‘Vote’图标以"Apply new migrations"（应用新迁移）.

或者在浏览器直接访问：http://你的网站地址/backend/modupdate?name=vote

3.2 方式二: 通过命令行:
```
yiic yupe updateMigrations --modules=vote
```
如果提示 "There is no modules to update migrations."（没有要更新迁移的模块），可尝试执行
```
yiic yupe flushCache
```
注意：在 Yii 1.x下，如果"yiic migrate up --migrationPath=modules.vote.migrations" 不成功，可以用phpMyAdmin等工具直接导入表。

## 用法

Vote widget:

```php
<?php $this->widget('application.modules.vote.widgets.VoteWidget', [
	'entity' => 'itemVote',
	'model' => $model,
	'options' => ['class' => 'vote vote-visible-buttons'],
]); ?>
```

Like/Favorite widgets:

```php
<?php $this->widget('application.modules.vote.widgets.FavoriteWidget', [
	'entity' => 'itemFavorite',
	'model' => $model,
]); ?>

<?php $this->widget('application.modules.vote.widgets.LikeWidget', [
	'entity' => 'itemLike',
	'model' => $model,
]); ?>
```

## 变更记录

详情请参阅 [CHANGELOG](CHANGELOG.md)（暂无）.

## 已知问题
1. 在Android 4.1.2下各按钮点击无反应. 原因未明。推测可能是该浏览器不兼容 HTML5的自定义 Data属性(data-*).

## License

BSD 3-Clause License. 详情请参阅 [License File](LICENSE).
