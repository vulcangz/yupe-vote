<?php
/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package hauntd\vote\widgets
 */

/** 
 * @author Vulcangz <zhugd168@163.com>
 * @package vote
 */

require_once 'BaseWidget.php';
require_once 'VoteToggle.php';

class FavoriteWidget extends VoteToggle
{
    /**
     * @var string
     */
    public $jsCodeKey = 'vote-favorite';

    /**
     * @var string
     */
    public $viewFile = 'favorite';

	/**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array_merge(parent::getDefaultOptions(), [
            'class' => 'vote-toggle vote-toggle-favorite',
        ]);
    }

    /**
     * @return array
     */
    public function getDefaultButtonOptions()
    {
        return array_merge(parent::getDefaultButtonOptions(), [
            'icon' => "<i class='glyphicon glyphicon-star'></i>",
			'label' => Yii::t('voteModule.vote', 'Add to favorites'),
            'labelAdd' => Yii::t('voteModule.vote', 'Add to favorites'),
            'labelRemove' => Yii::t('voteModule.vote', 'Remove from favorites'),
        ]);
    }

    /**
     * Initialize with default events.
     *
     * @param string $selector
     */
    public function initJsEvents($selector)
    {
		parent::initJsEvents($selector);
        $this->jsChangeCounters = "
            if (data.success) {
                $('$selector .vote-count').text(data.aggregate.positive);
                var label = '';
                if (data.toggleValue) {
                    label = button.attr('data-label-remove');
                    button.addClass('vote-active');
                } else {
                    label = button.attr('data-label-add');
                    button.removeClass('vote-active');
					
                }
                button.find('.vote-label').text(label);
            }
			if (data.message) $.notify(data.message);
        ";
    }
}
