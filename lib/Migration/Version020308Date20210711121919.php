<?php

declare(strict_types=1);

namespace OCA\Passman\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version020308Date20210711121919 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('passman_vaults')) {
			$table = $schema->createTable('passman_vaults');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('guid', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('vault_settings', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('last_access', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('public_sharing_key', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('private_sharing_key', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('sharing_keys_generated', 'bigint', [
				'notnull' => false,
				'length' => 8,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['last_access'], 'passman_vault_last_access_index');
			$table->addIndex(['guid'], 'passman_vault_guid_index');
			$table->addIndex(['id'], 'npassman_vault_id_index');
			$table->addIndex(['user_id'], 'passman_vault_uid_id_index');
		}

		if (!$schema->hasTable('passman_credentials')) {
			$table = $schema->createTable('passman_credentials');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('guid', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('vault_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('label', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('description', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('changed', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('tags', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('email', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('username', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('password', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('url', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('renew_interval', 'bigint', [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('expire_time', 'bigint', [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('delete_time', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('files', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('custom_fields', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('otp', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('hidden', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('compromised', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('shared_key', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('icon', 'text', [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['id'], 'passman_credential_id_index');
			$table->addIndex(['vault_id'], 'passman_credential_vault_id_index');
			$table->addIndex(['user_id'], 'passman_credential_user_id_index');
		}

		if (!$schema->hasTable('passman_files')) {
			$table = $schema->createTable('passman_files');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('guid', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('mimetype', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('filename', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('size', 'bigint', [
				'notnull' => true,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => true,
			]);
			$table->addColumn('file_data', 'text', [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['id'], 'passman_file_id_index');
			$table->addIndex(['user_id'], 'passman_file_user_id_index');
		}

		if (!$schema->hasTable('passman_revisions')) {
			$table = $schema->createTable('passman_revisions');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('guid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('credential_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('credential_data', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('edited_by', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['id'], 'passman_revision_id_index');
			$table->addIndex(['user_id'], 'passman_revision_user_id_index');
			$table->addIndex(['credential_id'], 'passman_revision_credential_id_index');
		}

		if (!$schema->hasTable('passman_sharing_acl')) {
			$table = $schema->createTable('passman_sharing_acl');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('item_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('item_guid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('vault_id', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('vault_guid', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => false,
				'length' => 64,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('expire', 'bigint', [
				'notnull' => false,
				'length' => 64,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('expire_views', 'bigint', [
				'notnull' => false,
				'length' => 64,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('permissions', 'smallint', [
				'notnull' => true,
				'length' => 3,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('shared_key', 'text', [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('passman_share_request')) {
			$table = $schema->createTable('passman_share_request');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('item_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('item_guid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('target_user_id', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('from_user_id', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('target_vault_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('target_vault_guid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('shared_key', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('permissions', 'smallint', [
				'notnull' => true,
				'length' => 3,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => false,
				'length' => 64,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('passman_delete_vault_request')) {
			$table = $schema->createTable('passman_delete_vault_request');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('vault_guid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('reason', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('requested_by', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('created', 'bigint', [
				'notnull' => false,
				'length' => 64,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->setPrimaryKey(['id']);
		}
		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
