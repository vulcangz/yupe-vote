<?php

use yupe\components\controllers\FrontController;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package hauntd\vote\controllers
 */

/** 
 * @author Vulcangz <zhugd168@163.com>
 * @package vote
 */

class DefaultController extends FrontController
{
    /**
     * @var string
     */
    public $defaultAction = 'vote';

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'vote' => [
				'class' => 'application.modules.vote.controllers.actions.VoteAction',
			],
        ];
    }
}