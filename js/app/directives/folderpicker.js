/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2018, Felix Nüsse (felix.nuesse@t-online.de)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function() {
  'use strict';

  /**
   * @ngdoc directive
   * @name passmanApp.directive:folderpicker
   * @description
   * # folderpicker
   */
  angular.module('passmanApp').directive('folderPicker', [
	  '$window', '$http', 'CredentialService', 'NotificationService', '$translate', '$rootScope', function($window, $http, CredentialService, NotificationService, $translate, $rootScope) {
      return {
          templateUrl: 'views/partials/folder-picker.html',
          restrict: 'A',
          scope: {
              credential: '=folderPicker',
              folder: '=folder'
          },
          link: function(scope, element) {

              scope.currentFolder = scope.credential.folderpath;
              scope.tempFolderList = [];
              scope.BreadcrumbList = ["test","1"];
              scope.enableInput = false;

              scope.toggleInput = function() {
                  scope.enableInput = !scope.enableInput;
              };

			  scope.save = function() {
				  CredentialService.updateCredential(scope.credential).then(function (updated_credential) {
					  NotificationService.showNotification($translate.instant('folderpath.moved'), 5000);
					  $rootScope.$broadcast('updateFolderInMainList', updated_credential);
					  //$('#folderPicker').dialog('close');
				  });
			  };

              scope.currentPathAddNew = function (nextFolder) {
                  if(typeof nextFolder === 'undefined'){
                      nextFolder=$translate.instant('folderpath.newfolder');
                  }
                  scope.currentPathAdd(nextFolder);
                  scope.enableInput=false;
              };

              scope.currentPathAdd = function (nextFolder) {
                  console.log(scope.tempFolderList)
                  scope.currentFolder+='/'+nextFolder;
                  scope.getCurrentFolderList();
                  scope.setCurrentFolderFromBreadcrumb(scope.currentFolder);
              };

              scope.getCurrentFolderList = function () {
                  var Temp=[];
                  for (var i=0; i<scope.folder.length; i++) {
                      if(scope.folder[i].startsWith(scope.currentFolder) && scope.checkIfFolderIsSubfolder(scope.folder[i])){
                          var reducedFoldername=scope.cutScopeFolderFromFoldername(scope.folder[i]);
                          if(Temp.indexOf(reducedFoldername) <= -1){
                              Temp.push(reducedFoldername);
                          }
                      }
                  }
                  Temp.splice( Temp.indexOf(""), 1 );
                  scope.tempFolderList=Temp;
              };

              scope.checkIfFolderIsSubfolder = function (folder) {
                  return folder.startsWith(scope.currentFolder);
              };

              scope.cutScopeFolderFromFoldername = function (folder) {
                  var withoutParent=folder.replace(scope.currentFolder, "")+"/";
                  var withoutRest="";
                  var temp="";
                  if(withoutParent.startsWith("/")){
                      temp=withoutParent.substring(1,withoutParent.length);
                      withoutRest=temp.substring(0,temp.indexOf("/"));
                  }else{
                      withoutRest=withoutParent.substring(0,withoutParent.indexOf("/"));
                  }

                  return withoutRest;
              };

              scope.createBreadCrumbList = function () {
                  var array= scope.currentFolder.split("/").filter(Boolean);
                  var res=[];
                  var fullPath="/";
                  array.forEach(function (element) {
                      fullPath+=element+"/";
                      res.push({fullPath:fullPath, name:element});
                  });
                  scope.BreadcrumbList=res;

              };

              scope.setCurrentFolderFromBreadcrumb = function (folder) {
                  scope.currentFolder=folder;
                  scope.createBreadCrumbList();
                  scope.getCurrentFolderList();
              };

              scope.createBreadCrumbList();
              scope.getCurrentFolderList();

              scope.close = function() {

              };

              $(element).click(function() {
                  $('#folderPicker').dialog({
                      width: 570,
                      //height: 140,
                      height: 470,
					  classes: {
						  "ui-dialog": "ui-dialog folderpicker-dialog"
					  },
                      close: function() {
                          $(this).dialog('destroy');
                      }
                  });
              });
          }
      };
  }]);
}());
