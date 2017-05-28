<?php
	return [
    'module'   => [
		'class' => 'application.modules.vote.VoteModule',
		'guestTimeLimit' => 3600,
		'entities' => [
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
		],
    ],
    'import'    => [
		'application.modules.vote.VoteModule',
		'application.modules.vote.models.*',
		'application.modules.vote.forms.*',
		'application.modules.vote.components.*',
    ],
    'component' => [ ],
    'rules'     => [
		'/vote/vote' => 'vote/default/vote',
	],
];