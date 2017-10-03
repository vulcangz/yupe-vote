<?php
/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package hauntd\vote
 */
 
/**
 * @author vulcangz <zhugd168@163.com>
 * @package vote
 */

class VoteModule extends yupe\components\WebModule
{
	/**
     *
     */
    const VERSION = '0.11';
	
    const TYPE_VOTING = 'voting';
    const TYPE_TOGGLE = 'toggle';

    /**
     * @var array Entities that will be used by vote widgets.
     * - `$modelName`: model class name
     * - `$allowGuests`: allow users to vote
     * - `$type`: vote type (Module::TYPE_VOTING or Module::TYPE_TOGGLE)
     */
    public $entities;

    /**
     * @var int
     */
    public $guestTimeLimit = 3600; // 1 hour per vote for guests

    /**
     * @var string
     */
    public $redirectUrl = '/site/login';
	
	/**
     * @return array
     */
    public function getDependencies()
    {
        return [
            'user',
        ];
    }
	
    /**
     * @param $entity
     * @return int
     */
    public function encodeEntity($entity)
    {
        return sprintf('%u', crc32($entity));
    }

    /**
     * @param $entity
     * @return array|null
     * @throws CException
     */
    public function getSettingsForEntity($entity)
    {
        if (!isset($this->entities[$entity])) {
            return null;
        }
        $settings = $this->entities[$entity];
        if (!is_array($settings)) {
            $settings = ['modelName' => $settings];
        }
        $settings = array_merge($this->getDefaultSettings(), $settings);
        if (!in_array($settings['type'], [self::TYPE_TOGGLE, self::TYPE_VOTING])) {
            throw new CException('Unsupported voting type.');
        }

        return $settings;
    }

    /**
     * @return array
     */
    protected function getDefaultSettings()
    {
        return [
            'type' => self::TYPE_VOTING,
            'allowGuests' => false,
        ];
    }
	
	
    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }
	
	/**
     * @return bool
     */
    public function getAdminPageLink()
    {
        return '/vote/voteBackend/index';
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return Yii::t('VoteModule.vote', 'Services');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return Yii::t('VoteModule.vote', 'Vote');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Yii::t('VoteModule.vote', 'This module allows you to attach vote widgets, like/favorite buttons to your models.');
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return Yii::t('VoteModule.vote', 'vulcangz');
    }

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return Yii::t('VoteModule.vote', 'zhugd168@163.com');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return 'http://github.com/vulcangz/yupe-vote';
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'fa fa-fw fa-thumbs-o-up';
    }

    /**
     *
     */
    public function init()
    {		
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
        $this->setImport(array(
            'vote.models.*',
            'vote.forms.*',
            'vote.components.*',
        ));
    }
	
	/**
     * @return array
     */
    public function getAuthItems()
    {
        return [
            [
                'name' => 'Vote.VoteManager',
                'description' => Yii::t('VoteModule.vote', 'Manage vote'),
                'type' => AuthItem::TYPE_TASK,
                'items' => [
                    [
                        'type' => AuthItem::TYPE_OPERATION,
                        'name' => 'Vote.VoteBackend.Create',
                        'description' => Yii::t('VoteModule.vote', 'Creating vote'),
                    ],
                    [
                        'type' => AuthItem::TYPE_OPERATION,
                        'name' => 'Vote.VoteBackend.Delete',
                        'description' => Yii::t('VoteModule.vote', 'Removing vote'),
                    ],
                    [
                        'type' => AuthItem::TYPE_OPERATION,
                        'name' => 'Vote.VoteBackend.Index',
                        'description' => Yii::t('VoteModule.vote', 'List of vote'),
                    ],
                    [
                        'type' => AuthItem::TYPE_OPERATION,
                        'name' => 'Vote.VoteBackend.Update',
                        'description' => Yii::t('VoteModule.vote', 'Editing vote'),
                    ],
                    [
                        'type' => AuthItem::TYPE_OPERATION,
                        'name' => 'Vote.VoteBackend.View',
                        'description' => Yii::t('VoteModule.vote', 'Viewing vote'),
                    ],
                ],
            ],
        ];
    }
}
