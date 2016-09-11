angular.module('templates-main', ['views/show_vault.html', 'views/vaults.html']);

angular.module('views/show_vault.html', []).run(['$templateCache', function($templateCache) {
  'use strict';
  $templateCache.put('views/show_vault.html',
    'Welcome to the vault! {{active_vault}} Credentials: {{credentials}}');
}]);

angular.module('views/vaults.html', []).run(['$templateCache', function($templateCache) {
  'use strict';
  $templateCache.put('views/vaults.html',
    '<div class="vault_wrapper"><div class="vaults" ng-if="!list_selected_vault && !creating_vault"><div class="ui-select-container ui-select-bootstrap vaultlist"><ul><li ng-click="newVault()">+ Create a new vault</li><li ng-repeat="vault in vaults" ng-class="{\'selected\': vault == list_selected_vault }" ng-click="selectVault(vault)"><div><span class="ui-select-choices-row-inner"><div class="ng-binding ng-scope">{{vault.name}}</div><small class="ng-binding ng-scope">Created: {{vault.created * 1000 | date:\'dd-MM-yyyy @ HH:mm:ss\'}} | Last accessed: <span ng-if="vault.last_access > 0">{{vault.last_access * 1000 | date:\'dd-MM-yyyy @ HH:mm:ss\'}}</span> <span ng-if="vault.last_access === 0">Never</span></small></span></div></li><li ng-if="vaults.length === 0">No vaults found, why not create one?</li></ul></div></div><div ng-if="creating_vault"><div class="login_form" ng-init="vault_name = \'\' ">Please give your new vault a name.<div><input type="text" ng-model="vault_name"></div><div>Vault password <input type="text" ng-model="vault_key"></div><div>Repeat vault password <input type="text" ng-model="vault_key2"></div><div class="button_wrapper"><div class="button button-geen" ng-click="createVault(vault_name, vault_key, vault_key2)">Create vault</div><div class="button button-red" ng-click="clearState()">Cancel</div></div></div></div><div ng-if="list_selected_vault != false"><div class="vaultlist"><ul><li ng-click="clearState()">Go back to vaults</li></ul></div><div class="login_form"><div ng-show="error" class="error"><ul><li>{{error}}</li></ul></div>Please input the password for {{list_selected_vault.name}}<div><input type="password" ng-model="vault_key"></div><div><div><label><input type="checkbox" ng-checked="default_vault" ng-click="toggleDefaultVault()"> Set this vault as default.</label></div><div><label><input type="checkbox" ng-checked="remember_vault_password" ng-click="toggleRememberPassword()"> Login automatically to this vault.</label></div></div><div class="button button-geen" ng-click="loginToVault(list_selected_vault, vault_key)">Decrypt vault</div></div></div></div>');
}]);
