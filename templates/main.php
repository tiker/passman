<?php
\OCP\Util::addscript('passman', 'sjcl');
\OCP\Util::addscript('passman', 'angular.min');
\OCP\Util::addscript('passman', 'tagsInput.min');
\OCP\Util::addscript('passman', 'bower_components/ng-clip/dest/ng-clip.min');
\OCP\Util::addscript('passman', 'bower_components/zeroclipboard/dist/ZeroClipboard.min');
\OCP\Util::addscript('passman', 'jstorage');
\OCP\Util::addscript('passman', 'bower_components/zxcvbn/zxcvbn-async');
\OCP\Util::addscript('passman', 'pwgen');
\OCP\Util::addscript('passman', 'ng-click-select');
\OCP\Util::addscript('passman', 'qrReader/llqrcode');
\OCP\Util::addscript('passman', 'sha');
\OCP\Util::addscript('passman', 'func');
\OCP\Util::addscript('passman', 'app');
\OCP\Util::addscript('passman', 'app.service');
\OCP\Util::addscript('passman', 'app.directive');
\OCP\Util::addscript('passman', 'app.filter');
\OCP\Util::addScript('passman', 'jsrsasign-4.7.0-all-min');


\OCP\Util::addStyle('passman', 'ocPassman');
\OCP\Util::addStyle('passman', 'ng-tags-input.min');
\OCP\Util::addStyle('passman', 'bootstrapGrid');


?>
<div ng-app="passman" id="app" ng-controller="appCtrl">
  <div class="loaderContainer" hide-loaded>
    <div class="loader"></div>
    <div class="text">Loading....</div>
  </div>
  <div id="app-navigation" ng-controller="navigationCtrl" style="display: none" show-loaded>
    <div id="searchTagContainer">
      <tags-input ng-model="selectedTags" removeTagSymbol="x" replace-spaces-with-dashes="false" min-length="1">
        <auto-complete source="loadTags($query)" min-length="1"></auto-complete>
      </tags-input>
      <span>Related Tags</span>
    </div>
    <ul id="tagList">
      <li class="tag" ng-click="selectTag(tag)" ng-repeat="tag in tags" ng-mouseover="mouseOver = true"
          ng-mouseleave="mouseOver = false">
        <span class="value">{{tag}}</span>
        <i ng-show="mouseOver" ng-click="tagSettings(tag,$event);" class="icon icon-settings button"></i>
      </li>
    </ul>

    <!-- TAG Settings dialog here, so it is in the scope of navigationCtrl -->
    <div id="tagSettingsDialog" style="display: none;">
      <form id="tagSettings">
        <label for="edit_folder_complexity" class="label_cpm">Label:</label><br/>
        <input type="text" ng-model="tagProps.tag_label"/><br/>
        <label for="edit_folder_complexity" class="label_cpm">Required password score:</label><br/>
        <input type="text" ng-model="tagProps.min_pw_strength"><br/>
        <label for="renewal_period" class="label_cpm">Renewal period (days):</label><br/>
        <input type="text" ng-model="tagProps.renewal_period">
      </form>
    </div>
    <div class="nav-trashbin" ng-click="selectTag('is:Deleted')"><i class="icon-delete icon"></i><a
          href="#">Deleted passwords</a></div>

    <div id="app-settings">
      <div id="app-settings-header">
        <button class="settings-button" data-apps-slide-toggle="#app-settings-content"></button>
      </div>
      <div id="app-settings-content">
        <p class="link" ng-click="showSettings();">Settings</p>

        <p class="import link">Import data</p>

        <div id="sessionTimeContainer" ng-show="sessionExpireTime!=0">
          <h2>Session time</h2>
          <em>Your session will expire in:<br/> <span ng-bind="sessionExpireTime"></span></em>
        </div>
        <p><a class="link" ng-click="lockSession()">Lock session</a></p>
      </div>
    </div>
  </div>
  <div id="app-content" ng-controller="contentCtrl" style="display: none" show-loaded>
    <div id="topContent">
      <button class="button" id="addItem" ng-click="addItem()">Add item</button>
      <button class="button" id="editItem" ng-click="editItem(currentItem)"
              ng-show="currentItem">Edit item
      </button>
      <button class="button" id="deleteItem" ng-click="deleteItem(currentItem,true)"
              ng-show="currentItem">Delete item
      </button>
      <input type="text" id="itemSearch" ng-model="itemFilter"
             class="visible-md visible-lg visible-sm pull-right searchbox" placeholder="Search..."/>
    </div>
    <ul id="pwList">
      <li ng-repeat="item in items | orderBy: 'label' | filter: {'label': itemFilter}"
          ng-mouseleave="toggle.state = false" ng-click="showItem(item);" ng-dblclick="editItem(item)"
          ng-class="{'row-active': item.id === currentItem.id}">
        <!-- if no image proxy -->
        <img ng-src="{{item.favicon}}" fallback-src="noFavIcon"
             style="height: 16px; width: 16px; float: left; margin-left: 8px; margin-right: 4px; margin-top: 5px;"
             ng-if="item.favicon && !userSettings.settings.useImageProxy">
        <img style="height: 16px; width: 16px; float: left; margin-left: 8px; margin-right: 4px; margin-top: 5px;"
             ng-src="{{noFavIcon}}" ng-if="!item.favicon && !userSettings.settings.useImageProxy">
        <!-- end if -->

        <!-- If image proxy === true -->
        <img image-proxy image="item.favicon" fallback="noFavIcon"
             style="height: 16px; width: 16px; float: left; margin-left: 8px; margin-right: 4px; margin-top: 5px;"
             ng-if="userSettings.settings.useImageProxy">
        <!--- // end  if-->
        <div style="display: inline-block;" class="itemLabel">{{item.label}}</div>
        <i class="icon-rename icon" ng-click="editItem(item)" title="Edit"></i>
        <ul class="editMenu">
          <li ng-click="toggle.state = !toggle.state" ng-class="{'show' : toggle.state}"
              off-click=' toggle.state = false'
              off-click-if='toggle.state'>
            <span class="icon-caret-dark more"></span>
            <ul ng-if="!showingDeletedItems">
              <li><a ng-click="editItem(item)">Edit</a></li>
              <li><a ng-click="shareItem(item)">Share</a></li>
              <li><a ng-click="showRevisions(item)">Revisions</a></li>
              <li><a ng-click="deleteItem(item,true )">Delete</a></li>
            </ul>
            <ul ng-if="showingDeletedItems">
              <li><a ng-click="recoverItem(item)">Restore</a></li>
              <li><a ng-click="deleteItem(item,false)">Destroy</a></li>
            </ul>
          </li>
        </ul>
        <div class="tag" ng-repeat="ttag in item.tags" ng-click="selectTag(ttag.text)"><span
              class="value">{{ttag.text}}</span></div>
      </li>
    </ul>
    <div id="infoContainer">
      <table>
        <tbody>
        <tr ng-show="currentItem.label">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>Label</span>:
          </td>
          <td>
            {{currentItem.label}} <a clip-copy="currentItem.label" clip-click="copied('label')" class="link">[Copy]</a>
          </td>
        </tr>
        <tr ng-show="currentItem.description">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>Description</span> :
          </td>
          <td>
            <div ng-bind-html="currentItem.description  | to_trusted"></div>
            <a clip-copy="currentItem.description" clip-click="copied('description')" class="link">[Copy]</a>
          </td>
        </tr>
        <tr ng-show="currentItem.account ">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>Account</span> :
          </td>
          <td>
            {{currentItem.account}} <a clip-copy="currentItem.account" clip-click="copied('account')"
                                       class="link">[Copy]</a>
          </td>
        </tr>
        <tr ng-show="currentItem.password ">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>Password</span> :
          </td>
          <td>
            <span pw="currentItem.password" toggle-text-stars></span> <a clip-copy="currentItem.password"
                                                                         clip-click="copied('password')"
                                                                         class="link">[Copy]</a>
          </td>
        </tr>
        <tr ng-if="currentItem.otpsecret ">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>One time password</span> :
          </td>
          <td>
            &nbsp;<span otp-generator otpdata="currentItem.otpsecret.secret"></span>
          </td>
        </tr>
        <tr ng-show="currentItem.expire_time">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>Expires</span> :
          </td>
          <td>
            {{currentItem.expire_time | date}}
          </td>
        </tr>
        <tr ng-show="currentItem.email ">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>Email</span> :
          </td>
          <td>
            {{currentItem.email}} <a clip-copy="currentItem.email" clip-click="copied('E-mail')" class="link">[Copy]</a>
          </td>
        </tr>
        <tr ng-show="currentItem.url ">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float: left; margin-right: .3em;">&nbsp;</span>
            <span>URL</span> :
          </td>
          <td>
            {{currentItem.url}} <a clip-copy="currentItem.url" clip-click="copied('URL')" class="link">[Copy]</a> <a
                ng-href="{{currentItem.url}}" class="link" target="_blank">[Open]</a>
          </td>
        </tr>
        <tr ng-show="currentItem.files.length > 0 && currentItem.files">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float:left; margin-right:.3em;">&nbsp;</span>
            <span>Files & Images</span> :
          </td>
          <td>
            <span ng-repeat="file in currentItem.files" class="link loadFile" ng-click="loadFile(file)"><span
                  ng-class="file.icon"></span>{{file.filename}}  ({{file.size | bytes}})</span>
          </td>
        </tr>
        <tr ng-show="currentItem.customFields.length > 0" ng-repeat="custom in currentItem.customFields">
          <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                  style="float:left; margin-right:.3em;">&nbsp;</span>
            {{custom.label}} :
          </td>
          <td>
                      <span ng-if="custom.clicktoshow==0">
                        {{custom.value}} <a clip-copy="custom.value" clip-click="copied(custom.label)"
                                            class="link">[Copy]</a>
                      </span>
                      <span ng-if="custom.clicktoshow==1">
                       <span pw="custom.value" toggle-text-stars></span> <a clip-copy="custom.value"
                                                                            clip-click="copied(custom.label)"
                                                                            class="link">[Copy]</a>
                      </span>
          </td>
        </tr>
        </tbody>
      </table>
      <table id="customFieldsTable">

      </table>
    </div>
    <!-- end InfoContainer -->

    <!-- Add / edit item -->
    <div id="editAddItemDialog" style="display: none;" ng-controller="addEditItemCtrl">
      <div class="error" ng-show="errors">
        <div ng-repeat="error in errors">{{error}}</div>
      </div>
      <form method="get" name="new_item" id="editNewItem">
        <div class="tabHeader" ng-class="'tab'+tabActive" ng-init="tabActive=1">
          <div class="col-xs-2 nopadding tab1" ng-click="tabActive=1;" ng-class="{'active': tabActive==1}">General</div>
          <div class="col-xs-2 nopadding tab2" ng-click="tabActive=2;" ng-class="{'active': tabActive==2}">Password
          </div>
          <div class="col-xs-2 nopadding tab3" ng-click="tabActive=3; " ng-class="{'active': tabActive==3}"
               ng-show="currentItem.id">Files
          </div>
          <div class="col-xs-3 nopadding tab4" ng-click="tabActive=4" ng-class="{'active': tabActive==4}">Custom
            fields
          </div>
          <div class="col-xs-3 nopadding tab5" ng-click="tabActive=5" ng-class="{'active': tabActive==5}">OTP settings
          </div>
        </div>
        <div class="row nomargin" ng-show="tabActive==1">
          <div class="row">
            <div class="col-xs-1 formLabel">Label</div>
            <div class="col-xs-7"><input type="text" ng-model="currentItem.label" autocomplete="off" id="labell"
                                         required></div>
            <div class="col-xs-1"><!-- if no image proxy -->
              <img ng-src="{{currentItem.favicon}}" fallback-src="noFavIcon"
                   style="height: 16px; width: 16px; float: left; margin-left: 8px; margin-right: 4px; margin-top: 5px;"
                   ng-if="currentItem.favicon && !userSettings.settings.useImageProxy">
              <img style="height: 16px; width: 16px; float: left; margin-left: 8px; margin-right: 4px; margin-top: 5px;"
                   ng-src="{{noFavIcon}}" ng-if="!currentItem.favicon && !userSettings.settings.useImageProxy">
              <!-- end if -->

              <!-- If image proxy === true -->
              <img image-proxy image="currentItem.favicon" fallback="noFavIcon"
                   style="height: 16px; width: 16px; float: left; margin-left: 8px; margin-right: 4px; margin-top: 5px;"
                   ng-if="userSettings.settings.useImageProxy">
            </div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Description</div>
            <div class="col-xs-7"><textarea rows="4" name="desc" id="desc" ng-model="currentItem.description"
                                            cols="3"></textarea></div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Login</div>
            <div class="col-xs-7"><input type="text" name="account" ng-model="currentItem.account" id="account"
                                         autocomplete="off"></div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Email</div>
            <div class="col-xs-7"><input type="text" name="email" ng-model="currentItem.email" autocomplete="off"></div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">URL</div>
            <div class="col-xs-7"><input type="text" name="url" ng-model="currentItem.url" autocomplete="off"
                                         ng-blur="updateFavIcon()"></div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Icon</div>
            <div class="col-xs-7"><input type="text" name="url" ng-model="currentItem.favicon" autocomplete="off"></div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Tags</div>
            <div class="col-xs-7">
              <tags-input ng-model="currentItem.tags" class="inputCurrentTags" removeTagSymbol="x" min-length="1"
                          replace-spaces-with-dashes="false">
                <auto-complete source="loadTags($query)" min-length="1" max-results-to-show="2"></auto-complete>
              </tags-input>
            </div>
            <div class="col-xs-9">
              <div class="currentTags">
                <div ng-repeat="tag in currentItem.tags" class="pull-left tag">
                  {{tag.text}} <span ng-click="removeTag(tag)" class="icon icon-delete"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row nomargin" ng-show="tabActive==2">
          <div class="row">
            <div class="col-xs-12 formLabel">Minimal password score: {{requiredPWStrength}}</div>
            <div class="col-xs-12">
              <input type="checkbox" ng-model="currentItem.overrrideComplex"><label class="label_cpm">Override required
                score</label>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Password</div>
            <div class="col-xs-5">
              <input ng-show="!pwFieldVisible" type="password" name="password" ng-model="currentItem.password"
                     autocomplete="off">
              <span ng-show="pwFieldVisible" class="pwPreview">{{currentItem.password}}</span>
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3 nopadding">
              <span class="icon icon-history" ng-click="generatePW(); usePw();"></span>
              <span title="Mask/Display the password" class="icon icon-toggle" ng-click="togglePWField()"></span>
              <a clip-copy="currentItem.password" clip-click="copied('password')"
                 class="ui-icon ui-icon-copy pull-right nomargin icon-copy"></a>
            </div>
          </div>
          <div class="row" ng-show="currentPWInfo">
            <div class="col-xs-11">
              <span>Current password score:</span> {{currentPWInfo.entropy}}<br/>
              <span>Crack time:</span><br>
              <small>{{currentPWInfo.crack_time | secondstohuman}}</small>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-1 formLabel">Password (again)</div>
            <div class="col-xs-5">
              <input type="password" ng-model="currentItem.passwordConfirm" autocomplete="off">
            </div>
          </div>
          <div class="row">
            <div class="col-xs-11">
            <span ng-show="!newExpireTime && currentItem.expire_time != 0">Password will expire at
              <span ng-bind="currentItem.expire_time | date"></span>
            </span>
            <span ng-show="newExpireTime">Password will expire at
              <span ng-bind="newExpireTime | date"></span>
            </span>
            </div>
          </div>
          <div class="row">
            <span ng-click="showPwSettings=true" class="link col-xs-12" ng-show="!showPwSettings">Show password generation settings</span>
            <span ng-click="showPwSettings=false" class="link col-xs-12" ng-show="showPwSettings">Hide password generation settings</span>

            <div id="pwTools" ng-show="showPwSettings">
          <span id="custom_pw">
              <span>Password Length</span>
              <input type="number" ng-model="pwSettings.length" style="width:30px"><br>
              <input type="checkbox" ng-model="pwSettings.upper"><label for="upper">A-Z</label> <input
                ng-model="pwSettings.lower" type="checkbox" id="lower"><label
                for="lower">a-z</label>
              <input ng-model="pwSettings.digits" type="checkbox" id="digits"><label
                for="digits">0-9</label>
              <input type="checkbox" id="special" ng-model="pwSettings.special"><label
                for="special">Special</label><br>
              <label for="mindigits">Minimum Digit Count</label> <input
                ng-model="pwSettings.mindigits" type="text" id="mindigits" style="width:30px"><br>
              <input type="checkbox" id="ambig" ng-model="pwSettings.ambig"><label
                for="ambig">Avoid Ambiguous Characters</label><br>
              <input type="checkbox" ng-model="pwSettings.reqevery" id="reqevery"><label
                for="reqevery">Require Every Character Type</label><br>
          </span>
              <!--button class="button" ng-click="generatePW()">Generate password</button>
              <button class="button" ng-show="generatedPW!=''"
                      ng-click="usePw()">Use password
              </button>
              <div ng-show="generatedPW"><span>Generated password:</span> <br />{{generatedPW}}</div>
              <b ng-show="generatedPW"><span>Generated password score</span>:
                                                                            {{pwInfo.entropy}}</b><br />
              <b ng-show="generatedPW"><span>Crack time</span>: {{pwInfo.crack_time | secondstohuman}}</b-->
            </div>
          </div>
        </div>
        <div class="row nomargin" ng-show="tabActive==3">
          <div class="row">
            <div class="col-xs-11">
              <input type="file" fileread="uploadQueue" item="currentItem"/>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-11">
              Existing files
              <ul id="fileList">
                <li ng-repeat="file in currentItem.files" class="fileListItem">{{file.filename}} ({{file.size | bytes}}) <span
                      class="icon icon-delete" style="float:right;" ng-click="deleteFile(file)"></span></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="row nomargin" ng-show="tabActive==4">
          <div class="row">
            <div class="col-xs-11">
              <h1>Add field</h1>
              <table style="width: 100%;" class="customFields">
                <thead>
                <tr>
                  <td>Label</td>
                  <td>Value</td>
                  <td colspan="2">Hidden?</td>
                </tr>
                </thead>
                <tr>
                  <td><input name="customFieldName" ng-model="newCustomfield.label" type="text"
                             placeholder="Enter field name"/>
                  </td>
                  <td><input name="customFieldValue" ng-model="newCustomfield.value" type="text"
                             placeholder="Enter field value"/>
                  </td>
                  <td><input type="checkbox" ng-model="newCustomfield.clicktoshow"/></td>
                  <td><span ng-click="addCField(newCustomfield)" class="icon-add icon"></span></td>
                </tr>
              </table>
              <hr class="blue">
              <h1>Existing fields</h1>
              <table style="width: 100%;" ng-show="currentItem.customFields.length > 0">
                <thead>
                <tr>
                  <td>Label</td>
                  <td>Value</td>
                  <td colspan="2">Hidden?</td>
                </tr>
                </thead>
                <tr ng-repeat="custom in currentItem.customFields">

                  <td valign="top" class="td_title">
                    <span click-for-input value="custom.label"></span></td>
                  <td>
                    <span click-for-input value="custom.value"></span>
                  </td>
                  <td>
                    <input type="checkbox" ng-checked="custom.clicktoshow==1" ng-model="custom.clicktoshow"/>
                  </td>
                  <td>
                    <i class="icon icon-delete" ng-click="removeCField(custom)"></i>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div class="row nomargin" ng-show="tabActive==5">

          <div class="col-xs-12">
            <div class="col-xs-2 nopadding">
              OTP type
            </div>
            <div class="col-xs-6 nopadding">
              <input type="radio" name="seletcOTPType" value="image" ng-model="otpType" id="otpImg"><label for="otpImg">Upload
                an image</label><br/>
              <input type="radio" name="seletcOTPType" value="string" ng-model="otpType" id="otpStr"><label
                  for="otpStr">Set the secret manually</label>
            </div>
            <div class="col-xs-12 nopadding">
              <input type="file" qrread on-read="parseQR(qrdata)" ng-show="otpType==='image'"/>
              <label ng-show="otpType==='string'">Enter the 2 factor secret <input type="text"
                                                                                   ng-model="currentItem.otpsecret.secret"
                                                                                   class="otpSecret"/></label>
            </div>
          </div>
          <hr>
          <div class="col-sm-12">Current OTP settings</div>
          <div class="col-sm-4">
            <img ng-src="{{currentItem.otpsecret.qrCode}}" ng-show="currentItem.otpsecret.qrCode" height="120"
                 width="120">
          </div>
          <div class="col-sm-4">
            <table ng-show="currentItem.otpsecret">
              <tr ng-show="currentItem.otpsecret.type">
                <td>Type:</td>
                <td>{{currentItem.otpsecret.type}}</td>
              </tr>
              <tr ng-show="currentItem.otpsecret.label">
                <td>Label:</td>
                <td>{{currentItem.otpsecret.label}}</td>
              </tr>
              <tr ng-show="currentItem.otpsecret.issuer">
                <td>Issuer:</td>
                <td>{{currentItem.otpsecret.issuer}}</td>
              </tr>
              <tr ng-show="currentItem.otpsecret.secret">
                <td>Secret:</td>
                <td><span pw="currentItem.otpsecret.secret" toggle-text-stars></span> <a
                      clip-copy="currentItem.otpsecret.secret" clip-click="copied('URL')" class="link">[Copy]</a></td>
              </tr>
            </table>
          </div>
        </div>
      </form>
      <button class="button cancel" ng-click="closeDialog()">Cancel</button>
      <button class="button save" ng-click="saveItem(currentItem)" ng-disabled="!new_item.$valid">Save</button>
    </div>
    <!-- end add / edit item -->




    <div id="dialog_files" style="display: none;">
      <img id="fileImg"/><br/>
      <span id="downloadImage"></span>
    </div>
    <div ng-controller="settingsCtrl" id="settingsDialog" ng-init="tabActive=1" style="display: none;">
      <div class="">
        <div class="col-md-12 tabHeader nopadding" ng-class="'tab'+tabActive">
          <div class="tab1 col-xs-3 col-md-2 nopadding" ng-click="tabActive=1" ng-class="{'active': tabActive==1}">
            General
          </div>
          <div class="tab2 col-xs-3 col-md-2 nopadding" ng-click="tabActive=2" ng-class="{'active': tabActive==2}">
            Sharing
          </div>
          <div class="tab3 col-xs-3 col-md-2 nopadding" ng-click="tabActive=3" ng-class="{'active': tabActive==3}">
            Tools
          </div>
          <div class="tab4 col-xs-3 col-md-2 nopadding" ng-click="tabActive=4" ng-class="{'active': tabActive==4}">
            Bookmarklet
          </div>
        </div>
        <div class="col-md-12">
          <div ng-show="tabActive==1" class="row">
            <div class="col-md-11">
              <h2>General settings</h2>

              <label><input type="checkbox" ng-model="userSettings.settings.useImageProxy"> Use image proxy on https
                pages</label>
            </div>
          </div>
        </div>
        <div ng-show="tabActive==2" class="row">
          <div class="col-sm-5">
            <label>Key size<select ng-model="userSettings.settings.sharing.shareKeySize">
                <option value="1024">Low (1024 bit)</option>
                <option value="2048">Medium (2048 bit)</option>
                <option value="4096">High (4096)</option>
              </select></label>
            Public key<br>
            <textarea
                style="width: 100%; height: 200px;">{{userSettings.settings.sharing.shareKeys.pubKeyObj}}</textarea>
          </div>
          <div class="col-sm-5">
            <label>Renew sharing keys: <input type="button" ng-click="renewShareKeys()" value="Renew"></label>
            Private key<br/>
            <textarea
                style="width: 100%; height: 200px;">{{userSettings.settings.sharing.shareKeys.prvKeyObj}}</textarea>
          </div>
        </div>
        <div ng-show="tabActive==3" class="row">
          <div class="col-md-11">
            <p>Here you can indentify weak passwords, we will list the items. List all password with a rating less
              than</p>
            <input type="text" ng-model="settings.PSC.minStrength"/>
            <button class="btn" ng-click="checkPasswords()">Show weak passwords</button>
            <div style="max-height: 300px; overflow-y: auto;">
              <table ng-table="tableParams" class="table" style="width: 100%;">
                <tr>
                  <td>Label</td>
                  <td>Score</td>
                  <td>Password</td>
                </tr>
                <tr ng-repeat="item in settings.PSC.weakItemList | orderBy:'score'">
                  <td>{{item.label}}</td>
                  <td>{{item.score}}</td>
                  <td><span pw="item.password" toggle-text-stars></span> <a
                        ng-click="showItem(item.originalItem); editItem(item.originalItem)" class="link">[edit]</a></td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div ng-show="tabActive==4" class="row">
          <div class="col-md-11">
            <p>Drag this to your browser bookmarks and click it, when you want to save username / password quickly</p>
            <br/>

            <p ng-bind-html="bookmarklet"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End contentCtrl -->

  <!--- Start sharing -->
  <div ng-controller="shareCtrl">
    <div id="shareDialog" style="display: none;" ng-init="tabActive=1">
      <div ng-show="userSettings.settings.sharing.shareKeys">
        <div class="tabHeader" ng-class="'tab'+tabActive">
          <div class="col-xs-4 tab1" ng-click="tabActive=1" ng-class="{'active': tabActive==1}">
            Users & Groups
          </div>
          <div class="col-xs-4 tab2" ng-click="tabActive=2" ng-class="{'active': tabActive==2}">
            Links
          </div>
        </div>
        <div class="row tabContent">
          <div class="col-md-6" ng-show="tabActive==1">
            Enter the users / groups you want to share the password with
            <tags-input ng-model="shareSettings.shareWith" removeTagSymbol="x" replace-spaces-with-dashes="false"
                        min-length="1">
              <auto-complete source="loadUserAndGroups($query)" min-length="1" max-results-to-show="6"></auto-complete>
            </tags-input>
            <table width="100%">
              <th>
                <tr>
                  <td>Name</td>
                  <td>Type</td>
                </tr>
              </th>
              <tr ng-repeat="sharetargets in shareSettings.shareWith">
                <td>{{sharetargets.text}}</td>
                <td>{{sharetargets.type}}</td>
              </tr>
            </table>
          </div>
          <div class="col-xs-8" ng-show="tabActive==2">
            <label><input type="checkbox" ng-model="shareSettings.allowShareLink" ng-click="createShareUrl()"/>Create
              share
              link</label>

            <div ng-show="shareSettings.allowShareLink">
              Your share link:
              <input type="text" ng-click-select ng-model="shareSettings.shareUrl" class="shareUrl"/>
            </div>
          </div>

        </div>
      </div>
      <div ng-show="!userSettings.settings.sharing.shareKeys">
        Generating sharing keys, this is a time time thing, please wait.
      </div>
    </div>
  </div>
  <!-- end sharing -->


  <div ng-controller="revisionCtrl" style="display: none;">
    <div id="revisions">
      <div class="row">
        <div class="col-md-10">
          <button class="btn btn-default pull-left" ng-click="compareSelected()">Compare selected</button>
          <button class="btn btn-default pull-left">Delete selected</button>
        </div>
      </div>
      <div class="row" ng-repeat="revision in revisions"  ng-class="{'even': $even} ">
        <div class="col-md-1 nopadding">
          <input type="checkbox" ng-model="revision.selected">
        </div>
        <div class="col-md-3">
          <span ng-if="revision.revision_date!== 'current'">{{revision.revision_date*1000 | date:"dd/MM/yyyy H:mm"}}<br /> by {{revision.user_id}}</span>
          <span ng-if="revision.revision_date=== 'current'">Current revision by {{revision.user_id}}</span>

        </div>
        <div class="col-md-6">
          {{revision.data.label}}
        </div>
        <div class="col-md-6">
          <a ng-click="showRevision(revision)" class="link">Show</a>
          <span ng-if="revision.revision_date!== 'current'"> | Restore</span>
        </div>
      </div>
    </div>
    <div id="showRevisions">
      <table style="width:100%">
        <tr>
          <td ng-repeat="showRevision in revisionCompareArr">
           <span ng-if="showRevision.revision_date!== 'current'">Revision of {{showRevision.revision_date*1000 | date:"dd/MM/yyyy H:mm"}} by {{showRevision.user_id}}</span>
           <span ng-if="showRevision.revision_date=== 'current'">Current revision by {{showRevision.user_id}}</span>
          </td>
        </tr>
        <tr>
          <td ng-repeat="showRevision in revisionCompareArr">
            <table class="revisionTable">
              <tbody>
              <tr ng-show="showRevision.data.label">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>Label</span>:
                </td>
                <td>
                  {{showRevision.data.label}}
                </td>
              </tr>
              <tr ng-show="showRevision.data.description">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>Description</span> :
                </td>
                <td>
                  <span ng-bind-html="showRevision.data.description  | to_trusted"></span>

                </td>
              </tr>
              <tr ng-show="showRevision.data.account ">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>Account</span> :
                </td>
                <td>
                  {{showRevision.data.account}}
                </td>
              </tr>
              <tr ng-show="showRevision.data.password ">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>Password</span> :
                </td>
                <td>
                  <span pw="showRevision.data.password" toggle-text-stars></span>
                </td>
              </tr>
              <tr ng-if="showRevision.data.otpsecret ">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>One time password</span> :
                </td>
                <td>
                  &nbsp; Yes
                </td>
              </tr>
              <tr ng-show="showRevision.data.expire_time">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>Expires</span> :
                </td>
                <td>
                  {{showRevision.data.expire_time | date}}
                </td>
              </tr>
              <tr ng-show="showRevision.data.email ">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>Email</span> :
                </td>
                <td>
                  {{showRevision.data.email}}
                </td>
              </tr>
              <tr ng-show="showRevision.data.url ">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float: left; margin-right: .3em;">&nbsp;</span>
                  <span>URL</span> :
                </td>
                <td>
                  {{showRevision.data.url}}
                </td>
              </tr>
              <tr ng-show="showRevision.data.files.length > 0 && showRevision.data.files">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float:left; margin-right:.3em;">&nbsp;</span>
                  <span>Files & Images</span> :
                </td>
                <td>
            <span ng-repeat="file in currentItem.files" class="link loadFile"><span
                  ng-class="file.icon"></span>{{file.filename}}  ({{file.size | bytes}})</span>
                </td>
              </tr>
              <tr ng-show="showRevision.data.customFields.length > 0" ng-repeat="custom in showRevision.data.customFields">
                <td valign="top" class="td_title"><span class="ui-icon ui-icon-carat-1-e"
                                                        style="float:left; margin-right:.3em;">&nbsp;</span>
                  {{custom.label}} :
                </td>
                <td>
                      <span ng-if="custom.clicktoshow==0">
                        {{custom.value}}
                      </span>
                      <span ng-if="custom.clicktoshow==1">
                       <span pw="custom.value" toggle-text-stars></span>
                      </span>
                </td>
              </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </table>

    </div>
</div>
<!-- End appCtrl -->

<!-- start revision dialog -->


<div id="encryptionKeyDialog" style="display: none;">
  <p>Enter your encryption key. If this if the first time you use Passman, this key will be used for encryption your
    passwords</p>
  <input type="password" id="ecKey" style="width: 150px;"/><br/>
  <input type="checkbox" id="ecRemember" name="ecRemember"/><label for="ecRemember">Remember this key</label>
  <select id="rememberTime">
    <option value="15">15 Minutes</option>
    <option value="15">30 Minutes</option>
    <option value="60">60 Minutes</option>
    <option value="180">3 Hours</option>
    <option value="480">8 Hours</option>
    <option value="1440">1 Day</option>
    <option value="10080">7 Days</option>
    <option value="43200">30 Days</option>
  </select>

</div>
</div>
