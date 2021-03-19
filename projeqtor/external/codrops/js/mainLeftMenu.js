/**
 * main.js
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2015, Codrops
 * http://www.codrops.com
 */
// florent ticket 4965
;(function(window) {

  'use strict';

  var support = { animations : Modernizr.cssanimations },
    animEndEventNames = { 'WebkitAnimation' : 'webkitAnimationEnd', 'OAnimation' : 'oAnimationEnd', 'msAnimation' : 'MSAnimationEnd', 'animation' : 'animationend' },
    animEndEventName = animEndEventNames[ Modernizr.prefixed( 'animation' ) ],
    onEndAnimation = function( el, callback ) {
      var onEndCallbackFn = function( ev ) {
        if( support.animations ) {
          if( ev.target != this ) return;
          this.removeEventListener( animEndEventName, onEndCallbackFn );
        }
        if( callback && typeof callback === 'function' ) { callback.call(); }
      };
      if( support.animations ) {
        el.addEventListener( animEndEventName, onEndCallbackFn );
      }
      else {
        onEndCallbackFn();
      }
    };

  function extend( a, b ) {
    for( var key in b ) { 
      if( b.hasOwnProperty( key ) ) {
        a[key] = b[key];
      }
    }
    return a;
  }

  function MLMenu(el, options) {
    this.el = el;
    this.options = extend( {}, this.options );
    extend( this.options, options );
    
    // the menus (<ul>´s)
    this.menus = [].slice.call(this.el.querySelectorAll('.menu__level'));

    // index of current menu
    // Each level is actually a different menu so 0 is root, 1 is sub-1, 2 sub-2, etc.
    this.current_menu = 0;

    /* Determine what current menu actually is */
    var current_menu;
    this.menus.forEach(function(menuEl, pos) {
      var items = menuEl.querySelectorAll('.menu__item');
      items.forEach(function(itemEl, iPos) {
        var currentLink = itemEl.querySelector('.menu__link--current');
        if (currentLink) {
          // This is the actual menu__level that should have current
          current_menu = pos;
        }
      });
    });

    if (current_menu) {
      this.current_menu = current_menu; 
    }

    this._init();
  }

  MLMenu.prototype.options = {
    // show breadcrumbs
    breadcrumbsCtrl : true,
    // initial breadcrumb text
    initialBreadcrumb : 'all',
    // show back button
    backCtrl : true,
    // delay between each menu item sliding animation
    itemsDelayInterval : 60,
    // direction 
    direction : 'r2l',
    // callback: item that doesn´t have a submenu gets clicked
    // onItemClick([event], [inner HTML of the clicked item])
    onItemClick : function(ev, itemName) { return false; }
  };

  MLMenu.prototype._init = function() {
    // iterate the existing menus and create an array of menus, 
    // more specifically an array of objects where each one holds the info of each menu element and its menu items
    this.menusArr = [];
    this.breadCrumbs = false;
    var self = this;
    var submenus = [];

    /* Loops over root level menu items */
    this.menus.forEach(function(menuEl, pos) {
      var menu = {menuEl : menuEl, menuItems : [].slice.call(menuEl.querySelectorAll('.menu__item'))};
      
      self.menusArr.push(menu);
      // set current menu class
      if( pos === self.current_menu ) {
        classie.add(menuEl, 'menu__level--current');
      }
      var menu_x = menuEl.getAttribute('data-menu');
      var links = menuEl.querySelectorAll('.menu__link');
      links.forEach(function(linkEl, lPos) {
        var submenu = linkEl.getAttribute('data-submenu');
        var idName = linkEl.getAttribute('id');
        if (submenu) {
          var pushMe = {"menu":submenu, "name": linkEl.textContent, "id": idName};
          if (submenus[pos]) {
            submenus[pos].push(pushMe);
          } else {
            submenus[pos] = []
            submenus[pos].push(pushMe);
          }
        }
      });
    });

    /* For each MENU, find their parent MENU */   
    this.menus.forEach(function(menuEl, pos) {
      var menu_x = menuEl.getAttribute('data-menu');
      submenus.forEach(function(subMenuEl, menu_root) {
        subMenuEl.forEach(function(subMenuItem, subPos) {
          
          if (subMenuItem.menu == menu_x) {
            self.menusArr[pos].backIdx = menu_root;
            self.menusArr[pos].name = subMenuItem.name;
            self.menusArr[pos].id = subMenuItem.id;
          }
        });
      });
    });

    // create breadcrumbs
    if( self.options.breadcrumbsCtrl ) {
      this.breadcrumbsCtrl = document.createElement('div');
      this.breadScrumLeft=dojo.byId('breadScrumb');
      this.breadcrumbsCtrl.className = 'menu__breadcrumbs';
      this.breadcrumbsCtrl.setAttribute('style', 'float:left;');
      this.el.insertBefore(this.breadcrumbsCtrl, this.el.firstChild);

      // add initial breadcrumb
      this._addBreadcrumb(0);
      
      // Need to add breadcrumbs for all parents of current submenu
      if (self.menusArr[self.current_menu] && self.menusArr[self.current_menu].backIdx != 0 && self.current_menu != 0) {
        this._crawlCrumbs(self.menusArr[self.current_menu].backIdx, self.menusArr);
        this.breadCrumbs = true;
      }

      // Create current submenu breadcrumb
      if (self.current_menu != 0) {
        this._addBreadcrumb(self.current_menu);
        this.breadCrumbs = true;
      }
    }
    this.researchDiv=document.createElement('div');
    this.researchIcon=document.createElement('div');
    this.clearSearchIcon=document.createElement('div');
    this.researchInput=document.createElement('input');
    this.hidStrreamButtonJs= document.createElement('div');
    this.hideButton = document.createElement('div');
    this.hideButton.className = 'iconHideMenuLeft iconSize32';
    this.hidStrreamButtonJs.className = 'hideStreamNewGui';
    this.researchDiv.className='researchMenuLeftMenu';
    this.researchInput.className='dijitReset dijitInputInner menuSearchInput';
    this.researchIcon.className='iconSearch  iconSize16 ';
    this.clearSearchIcon.className='iconCancel   iconSize16  imageColorNewGui clearSearchMenu';
    this.hidStrreamButtonJs.setAttribute('id', 'hideStreamNewGui');
    this.hidStrreamButtonJs.setAttribute('style', 'float:right;');
    this.researchInput.setAttribute('dojoType', 'dijit.form.TextBox');
    this.researchInput.setAttribute('onKeyUp', 'searchMenuToDisplay(this.value);');
    this.clearSearchIcon.setAttribute('onClick', 'clearSearchInputMenuLeft()');
    this.researchInput.setAttribute('id', 'menuSearchDiv');
    this.clearSearchIcon.setAttribute('id', 'clearSearchMenu');
    this.researchInput.placeholder= i18n('searchMenu');
    this.researchIcon.setAttribute('style', 'position:relative;float: left;left:8px;top:2px;');
    this.clearSearchIcon.setAttribute('style', 'display:none;');
    if(dojo.byId('isMenuLeftOpen').value=='false') this.hidStrreamButtonJs.style.display='none';
    this.el.insertBefore(this.hidStrreamButtonJs,  this.el.firstChild);
    this.hidStrreamButtonJs.insertAdjacentElement('afterbegin',  this.hideButton);
    this.breadcrumbsCtrl.insertAdjacentElement('afterend',this.researchDiv );
    this.researchDiv.insertAdjacentElement('afterbegin', this.researchIcon);
    this.researchDiv.insertAdjacentElement('afterbegin',  this.researchInput);
    this.researchDiv.insertAdjacentElement('beforeEnd',  this.clearSearchIcon);
    // event binding
    this._initEvents();
  };

  MLMenu.prototype._initEvents = function() {
    var self = this;

    for(var i = 0, len = this.menusArr.length; i < len; ++i) {
      this.menusArr[i].menuItems.forEach(function(item, pos) {
        item.querySelector('a').addEventListener('click', function(ev) { 
          var element=ev.target;
          if(ev.target.className=="divPosName" || ev.target.className.substr(-10)=="iconSize16")element=ev.target.parentNode;
          var submenu = element.getAttribute('data-submenu'),
            itemName = element.textContent,
            subMenuEl = self.el.querySelector('ul[data-menu="' + submenu + '"]');
          // check if there's a sub menu for this item
          if( submenu && subMenuEl ) {
            ev.preventDefault();
            // open it
            self._openSubMenu(subMenuEl, pos, itemName);
          }else {
            // add class current
            var currentlinks = self.el.querySelectorAll('.menu__link--current');
            if( currentlinks ) {
              currentlinks.forEach(function(el){
                classie.remove(el , 'menu__link--current');
              });
            }
            if(dojo.byId('parameterMenu')){
              var bootomMenuSelcet=dojo.byId('parameterMenu').querySelector('.menu__link--current');
              if(bootomMenuSelcet!=null)classie.remove(bootomMenuSelcet, 'menu__link--current');
            }
            var idcurentElement='#'+item.firstChild.getAttribute('id');
            var newCurrent=dojo.byId('ml-menu').querySelectorAll(idcurentElement);
            newCurrent.forEach(function(e){
              classie.add(e, 'menu__link--current');
            });
            
            // callback
            self.options.onItemClick(ev, itemName);
          }
        });
      });
    }
    
  };

  MLMenu.prototype._openSubMenu = function(subMenuEl, clickPosition, subMenuName) {
    if( this.isAnimating ) {
      return false;
    }
    this.isAnimating = true;
    
    // save "parent" menu index for back navigation
    this.menusArr[this.menus.indexOf(subMenuEl)].backIdx = this.current_menu;
    // save "parent" menu´s name
    this.menusArr[this.menus.indexOf(subMenuEl)].name = subMenuName;
    // current menu slides out
    this._menuOut(clickPosition);
    // next menu (submenu) slides in
    this._menuIn(subMenuEl, clickPosition);
  };

  MLMenu.prototype._back = function() {
    if( this.isAnimating ) {
      return false;
    }
    clearSearchInputMenuLeft();
    var currentMenu = this.menusArr[this.current_menu].menuEl;
   if(currentMenu.getAttribute('data-menu')!='main'){
     this.isAnimating = true;
      // current menu slides out
      this._menuOut();
      // next menu (previous menu) slides in
      var backMenu = this.menusArr[this.menusArr[this.current_menu].backIdx].menuEl;
      this._menuIn(backMenu);
  
      // remove last breadcrumb
      if( this.breadScrumLeft ) {
        this.breadScrumLeft.removeChild(this.breadScrumLeft.lastElementChild);
      }
      if(this.breadcrumbsCtrl){
        var idx = this.menus.indexOf(backMenu),
        name= idx ? this.menusArr[idx].name : this.options.initialBreadcrumb;
        var newBc = document.createElement('a');
        newBc.href = '#'; 
        newBc.innerHTML = name;
        this.breadcrumbsCtrl.replaceChild(newBc,this.breadcrumbsCtrl.lastElementChild);
      }
   }else{
     this.isAnimating = false;
   } 
  };

  MLMenu.prototype._menuOut = function(clickPosition) {
    // the current menu
    var self = this,
      currentMenu = this.menusArr[this.current_menu].menuEl,
      isBackNavigation = typeof clickPosition == 'undefined' ? true : false;
    // slide out current menu items - first, set the delays for the items
    this.menusArr[this.current_menu].menuItems.forEach(function(item, pos) {
      item.style.WebkitAnimationDelay = item.style.animationDelay = isBackNavigation ? parseInt(pos * self.options.itemsDelayInterval) + 'ms' : parseInt(Math.abs(clickPosition - pos) * self.options.itemsDelayInterval) + 'ms';
    });
    // animation class
    if( this.options.direction === 'r2l' ) {
      classie.add(currentMenu, !isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
    }
    else {
      classie.add(currentMenu, isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');  
    }
  };

  MLMenu.prototype._menuIn = function(nextMenuEl, clickPosition) {
    var self = this,
      // the current menu
      currentMenu = this.menusArr[this.current_menu].menuEl,
      isBackNavigation = typeof clickPosition == 'undefined' ? true : false,
      // index of the nextMenuEl
      nextMenuIdx = this.menus.indexOf(nextMenuEl);
        var nextMenu = this.menusArr[nextMenuIdx],
      nextMenuEl = nextMenu.menuEl,
      nextMenuItems = nextMenu.menuItems,
      nextMenuItemsTotal = nextMenuItems.length;

    // slide in next menu items - first, set the delays for the items
    nextMenuItems.forEach(function(item, pos) {
      item.style.WebkitAnimationDelay = item.style.animationDelay = isBackNavigation ? parseInt(pos * self.options.itemsDelayInterval) + 'ms' : parseInt(Math.abs(clickPosition - pos) * self.options.itemsDelayInterval) + 'ms';

      // we need to reset the classes once the last item animates in
      // the "last item" is the farthest from the clicked item
      // let's calculate the index of the farthest item
      var farthestIdx = clickPosition <= nextMenuItemsTotal/2 || isBackNavigation ? nextMenuItemsTotal - 1 : 0;

      if( pos === farthestIdx ) {
        onEndAnimation(item, function() {
          // reset classes
          if( self.options.direction === 'r2l' ) {
            classie.remove(currentMenu, !isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
            classie.remove(nextMenuEl, !isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
          }
          else {
            classie.remove(currentMenu, isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
            classie.remove(nextMenuEl, isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
          }
          classie.remove(currentMenu, 'menu__level--current');
          classie.add(nextMenuEl, 'menu__level--current');

          //reset current
          self.current_menu = nextMenuIdx;

          // control back button and breadcrumbs navigation elements
          if( !isBackNavigation ) {
            // add breadcrumb
            self._addBreadcrumb(nextMenuIdx);
          }
          else if( self.current_menu === 0 && self.breadcrumbsCtrl) {
            var buttonBack=self.breadcrumbsCtrl.querySelector('.iconButtonBackBreadScrum ');
            if(buttonBack){
              self.breadcrumbsCtrl.removeChild(buttonBack);
              self.breadcrumbsCtrl.style="cursor:initial;";

            }
          }

          // we can navigate again..
          self.isAnimating = false;

          // focus retention
          nextMenuEl.focus();
        });
      }
    });

    // animation class
    if( this.options.direction === 'r2l' ) {
      classie.add(nextMenuEl, !isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
    }
    else {
      classie.add(nextMenuEl, isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
    }
  };

  MLMenu.prototype._addBreadcrumb = function(idx) {
    if( !this.options.breadcrumbsCtrl ) {
      return false;
    }
    //florent
    var iconClass=  (idx )? ''+this.menusArr[idx].id : 'Home';
    iconClass= (idx)? iconClass.substr(3) : iconClass;
    
    
    var divBcl = document.createElement('div');
    if(iconClass=='Plan'){
      iconClass='Planning';
    }else if(iconClass=='Bill'){
      iconClass='Financial';
    }else if(iconClass=='Resources'){
      iconClass='Resource';
    }else if(iconClass=='GanttPlan'){
       iconClass='Planning';
    }
    divBcl.className='icon'+iconClass+' iconSize22 iconBreadSrumb';
    
    var name= idx ? this.menusArr[idx].name : this.options.initialBreadcrumb;
    divBcl.setAttribute('id', 'button'+name);
    divBcl.setAttribute('title', name);
    this.breadScrumLeft.appendChild(divBcl);
    
    
    var bc = document.createElement('a');
    bc.href = '#'; 
    bc.innerHTML = name;
    

    if(this.breadcrumbsCtrl.firstChild ){
      this.breadcrumbsCtrl.replaceChild(bc,this.breadcrumbsCtrl.lastElementChild);
      var newPreviousB=document.createElement('div');
      newPreviousB.setAttribute('class', 'iconButtonBackBreadScrum iconSize16 imageColorNewGuiSelected');
      newPreviousB.setAttribute('title', i18n('previous'));
      newPreviousB.setAttribute('style','float:left;background-size:12px !important;margin-top:8px;cursor:pointer !important;');
      var oldPreviousB=this.breadcrumbsCtrl.querySelector('.iconButtonBackBreadScrum ');
      this.breadcrumbsCtrl.style="cursor:pointer !important;";
      if(oldPreviousB){
        this.breadcrumbsCtrl.replaceChild(newPreviousB,oldPreviousB);
      }else{
        this.breadcrumbsCtrl.firstChild.insertAdjacentElement('beforebegin', newPreviousB);
      }
      // add event back
      var self = this;
      this.breadcrumbsCtrl.addEventListener('click', function(ev) {
        ev.preventDefault();
        self._back();
      });
    }else{
      this.breadcrumbsCtrl.appendChild(bc);
      this.breadcrumbsCtrl.addEventListener('click', function(ev) {
        clearSearchInputMenuLeft();
      });
    }
    
    var self = this;
    divBcl.addEventListener('click', function(ev) {
      ev.preventDefault();
      clearSearchInputMenuLeft();
      if(!divBcl.nextSibling || self.isAnimating) {
        return false;
      }
      
      self.isAnimating = true;
      
      // current menu slides out
      self._menuOut();
      // next menu slides in
      var nextMenu = self.menusArr[idx].menuEl;
      self._menuIn(nextMenu);

      var siblNode;
      //var siblingNode;
      while (siblNode = divBcl.nextSibling) {
        self.breadScrumLeft.removeChild(siblNode);
      }
      self.breadcrumbsCtrl.replaceChild(bc,self.breadcrumbsCtrl.lastElementChild);

    });

    
  };


  window.MLMenu = MLMenu;

})(window);