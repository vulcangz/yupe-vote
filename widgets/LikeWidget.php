<?php
//Yii::import('application.modules.vote.models.*');

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package vote.widgets
 */

/** 
 * @author Vulcangz <zhugd168@163.com>
 * @package vote
 */

require_once 'BaseWidget.php';
require_once 'VoteToggle.php';

class LikeWidget extends VoteToggle
{
    /**
     * @var string
     */
    public $jsCodeKey = 'vote-like';
	
	/**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array_merge(parent::getDefaultOptions(), [
            'class' => 'vote-toggle vote-toggle-like',
        ]);
    }

    /**
     * @return array
     */
    public function getDefaultButtonOptions()
    {
        return array_merge(parent::getDefaultButtonOptions(), [
            'icon' => "<i class='glyphicon glyphicon-heart'></i>",
            'label' => Yii::t('VoteModule.vote', 'Like'),
        ]);
    }
}
