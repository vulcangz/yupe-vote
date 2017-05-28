<?php

class m170527_134703_vote_index extends yupe\components\DbMigration
{
	public function safeUp()
	{
        $this->createIndex('vote_target_value_idx', '{{vote}}', 'entity, target_id, value', false);
		$this->createIndex('vote_target_user_idx', '{{vote}}', 'entity, target_id, user_id', false);
    }

	public function safeDown()
	{
        $this->dropIndex('vote_target_value_idx', '{{vote}}');
		$this->dropIndex('vote_target_user_idx', '{{vote}}');
    }
}