<?php
/**
 * Class VoteAction
 * @package hauntd\vote\actions
 */

/**
 * @author vulcangz <zhugd168@163.com>
 * @package vote
 */

Yii::import("application.modules.vote.models.*");
Yii::import("application.modules.vote.forms.*");

class VoteAction extends CAction
{
    /**
     * @return array
     * @throws CException
     */
    public function run()
    {
        if (!Yii::app()->request->getIsAjaxRequest() || !Yii::app()->request->getIsPostRequest()) {
            throw new \CHttpException(404);
        }

        $module = Yii::app()->getModule('vote');

        $form = new VoteForm();
		$form->unsetAttributes();
		if (Yii::app()->getRequest()->getParam('VoteForm')) {
            $data=Yii::app()->getRequest()->getParam('VoteForm');
			$form->entity=$data['entity'];
			$form->targetId=$data['targetId'];
			$form->action=$data['action'];
		}

		if ($form->validate()) {
			$settings = $module->getSettingsForEntity($data['entity']);
            if ($settings['type'] == $module::TYPE_VOTING) {
                $response = $this->processVote($form);
            } elseif ($settings['type'] == $module::TYPE_TOGGLE) {
                $response = $this->processToggle($form);
            }

            $_intEntity = $module->encodeEntity($data['entity']);

			// do not use 'select *', select only required fields.
			$q = 'SELECT positive, negative, rating FROM {{vote_aggregate}} WHERE entity=:entity and target_id=:target_id';
			$cmd = Yii::app()->db->createCommand($q);
			$cmd->bindParam(":entity", $_intEntity, PDO::PARAM_INT);
			$cmd->bindParam(":target_id", $data['targetId'], PDO::PARAM_INT);
			$response['aggregate'] = $cmd->queryRow();
        } else {
            $response = [
				'success' => false,
				'message' => $form->errors['entity'][0],
				//'message' => 'Data validation failed',
				//'errors' => $form->errors
			];
        }
        echo json_encode($response);
    }

    /**
     * Processes a vote (+/-) request.
     *
     * @param VoteForm $form
     * @return array
     * @throws CException
     */
    protected function processVote(VoteForm $form)
    {
        /* @var $vote Vote */
        $module = Yii::app()->getModule('vote');
		$_intEntity = $module->encodeEntity($form->entity);

        $response = ['success' => false];

        if (Yii::app()->getUser()->getIsGuest()) {
			$criteria = new CDbCriteria([
				'select' => 't.*',
				'params' => [],
			]);
			$criteria->addCondition('entity=:entity and target_id=:target_id and user_ip=:user_ip');
			$criteria->params[':entity'] = $_intEntity;
			$criteria->params[':target_id'] = $form->targetId;
			$criteria->params[':user_ip'] = Yii::app()->getRequest()->getUserHostAddress();
			$criteria->addCondition('(UNIX_TIMESTAMP() - created_at) < :timeLimit');
			$criteria->params[':timeLimit'] = $module->guestTimeLimit;
			$vote = Vote::model()->find($criteria);

			if ($vote) {
				$response = [
					'success' => false,
					'message' => Yii::t('voteModule.vote', 'Guests are restricted to vote once every {guestTimeLimit} seconds',array('{guestTimeLimit}'=>$module->guestTimeLimit)),
				];
				return $response;
			}
        } else {
			$vote = Vote::model()->find('entity=:entity AND target_id=:target_id AND user_id=:user_id', [
				':entity' => $_intEntity,
				':target_id' => $form->targetId,
				':user_id' => Yii::app()->getUser()->getId()
				]);
        }

        if ($vote == null) {
            $response = $this->createVote($_intEntity, $form->targetId, $form->getvalue());
        } else {
            if ((int)$vote->value !== $form->getvalue()) {
                $vote->value = $form->getvalue();
                if ($vote->save()) {
                    $response = [
						'success' => true, 
						'message' => Yii::t('voteModule.vote', 'Your vote has been changed. Thanks!'),
						'changed' => true
					];
                }
            } else{
                $response = [
                    'success' => false,
					'message' => Yii::t('voteModule.vote', 'You have already voted!'),
                ];
            }
        }

        return $response;
    }

    /**
     * Processes a vote toggle request (like/favorite etc).
     *
     * @param Vote $form
     * @return array
     * @throws CException
     */
    protected function processToggle(VoteForm $form)
    {
        /* @var $vote Vote */
        $module = Yii::app()->getModule('vote');
		$_intEntity = $module->encodeEntity($form->entity);

		if (Yii::app()->getUser()->getIsGuest()) {
			$vote = Vote::model()->find('entity=:entity and target_id=:target_id and user_ip=:user_ip', [
				':entity' => $_intEntity,
				':target_id' => $form->targetId,
                ':user_ip' => Yii::app()->getRequest()->getUserHostAddress()
			]);
		} else {
			$vote = Vote::model()->find('entity=:entity and target_id=:target_id and user_id=:user_id', [
				':entity' => $_intEntity,
				':target_id' => $form->targetId,
				':user_id' => Yii::app()->getUser()->getId()
			]);
		}

        if ($vote == null) {
            $response = $this->createVote($_intEntity, $form->targetId, $form->getvalue());
            $response['toggleValue'] = 1;
        } else {
            $vote->delete();
            $response = [
				'success' => true,
				'message' => Yii::t('voteModule.vote', 'Your vote has been canceled.'),
				'toggleValue' => 0
			];
        }

        return $response;
    }

    /**
     * Creates new vote entry and returns response data.
     *
     * @param string $entity
     * @param integer $target_id
     * @param integer $value
     * @return array
     */
    protected function createVote($entity, $target_id, $value)
    {
        $vote = new Vote();
        $vote->entity = $entity;
        $vote->target_id = $target_id;
        $vote->value = $value;
        $vote->user_id = Yii::app()->getUser()->getId();
        $vote->user_ip = Yii::app()->getRequest()->getUserHostAddress();

        if ($vote->save()) {
            return [
				'success' => true,
				'message' => Yii::t('voteModule.vote', 'Your vote is accepted. Thanks!'),
			];
        } else {
            return [
				'success' => false, 
				'message' => Yii::t('voteModule.vote', 'Vote saving failed.'),
				'errors' => $vote->errors
			];
        }
    }
}