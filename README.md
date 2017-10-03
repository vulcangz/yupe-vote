# yupe-vote

This module allows you to attach vote widgets, like/favorite buttons to your models (ported from [hauntd/yii2-vote](https://github.com/hauntd/yii2-vote)).

![Demo](https://github.com/vulcangz/yupe-vote/raw/master/docs/screenshot.gif)

- Attach as many widgets to model as you need
- Useful widgets included (Favorite button, Like button, Rating "up/down")

## Dependency

1. Yupe - CMS on Yii Framework 1.x (tested on yupe v1.1)

## Installation

### Step 1: Download and copy the vote folder to protected/modules/

### Step 2: Configuring your application

1. configure your module

1.1 Edit your module settings (template file: vote/install/vote.php), then copy it to protected/config/modules/

Entity names should be in camelCase like `itemVote`, `itemVoteGuests`, `itemLike` and `itemFavorite`.

```php
return [
  'modules' => [
    'vote' => [
      'class' => 'application.modules.vote.VoteModule',
        'guestTimeLimit' => 3600,
        'entities' => [
          // First Entity -> Settings
		  // Blog post Entity -> Settings
		  'postVote' => 'post', // your model
		  'postVoteGuests' => [
			  'modelName' => 'post', // your model
			  'allowGuests' => true,
		  ],
		  'postLike' => [
			  'modelName' => 'post', // your model
			  'type' => 'toggle', // TYPE_TOGGLE like/favorite button
		  ],
		  'postFavorite' => [
			  'modelName' => 'post', // your model
			  'type' => 'toggle', // like/favorite button
		  ],
		  // You can add other model Entity just like below
		  /*
          'itemVote' => 'product', // your model, such as 'product', 'post'
          'itemVoteGuests' => [
              'modelName' => 'product', // your model
              'allowGuests' => true,
          ],
          'itemLike' => [
              'modelName' => 'product', // your model
              'type' => 'toggle', // like/favorite button
          ],
          'itemFavorite' => [
              'modelName' => 'product', // your model
              'type' => 'toggle', // like/favorite button
          ], */
      ],
    ],
  ],
  'components' => [
    ...
  ]
];
```

1.2 Setup redis cache component for voteAttribute. If you already have redis cache component available, skip this step.
```php
'components' => [
	//...
	'cache' => [
		'class'=>'CRedisCache',
		'hostname'=>'127.0.0.1',
		'port'=>6379,
		'password'=> 'foobared',	//changed to your password
		'database'=>6,	//changed to your db
		'options'=>STREAM_CLIENT_CONNECT,
	],
	//...
```

2. Add behavior to you model

e.g. protected/modules/blog/models/Post.php

```php
public function behaviors()
{
	$module = Yii::app()->getModule('blog');

	return [
		//...			
		'vote' => [
			'class' => 'application.modules.vote.components.behaviors.VoteBehavior',
			'entity' => 'postVote',	// must be the same as you set in the previous step 1
			'cacheID' => 'cache'	// redis cache component ID you setup in "/protected/config/main.php"
		],
	];
}
```

3. Add vote widget to your views

e.g. Add all three widgets to view of blog/post/view (themes/default/views/blog/post/view.php)

```
<div class="row like_box">
	<div class="col-sm-12">
		<div class="pull-left">
			<?php $this->widget('application.modules.vote.widgets.LikeWidget', [
				'entity' => 'postLike',
				'model' => $post,
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

### Step 3: Updating database schema

After you downloaded and configured yupe-vote, the last thing you need to do is updating your database schema by applying the migrations:

1. Method 1: through Control panel of "Yupe!" 

After login into yupe backend, on index page - "Fast access to modules", click on the icon of Vote to "Apply new migrations".

or direct visit http://YourWebsiteUrl/backend/modupdate?name=vote

2. method 2: through Command line:

yiic yupe updateMigrations --modules=vote

If prompted "There is no modules to update migrations."

yiic yupe flushCache


## Usage

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

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Known issues
~~1. Click "no response" under the default browser of Android 4.1.2. 
Probably because it is not compatible with HTML5 Custom Data Attributes (data-*).~~
After adjusting the code call order of the js code, the problem is solved.

## License

BSD 3-Clause License. Please see [License File](LICENSE) for more information.
