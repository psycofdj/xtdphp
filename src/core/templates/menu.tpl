{*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
*}

<div class="navbar navbar-default navbar-inverse navbar-fixed-top">

  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-navbar">
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    {assign "brand" $__menu->getBrand() }
    <a class="navbar-brand hidden-sm" href="{$brand.url}">
      <img style="margin-top:-10px; margin-bottom:-10px; height:40px;" src="/img/favicon.png"/>
      {$brand.title}</a>
  </div>

  <div class="collapse navbar-collapse" id="menu-navbar">

    <ul class="nav navbar-nav">
      {foreach $__menu->getTabs() as $c_tab}
        {if false == $c_tab->isDisplayable()}
          {continue}
        {/if}

        {assign "active" ""}
        {if $c_tab->isActiveUrl()}
          {assign "active" "active"}
        {/if}

        {if $c_tab->hasTabs()}
          <li class="dropdown {$active}">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              {t}{$c_tab->m_title}{/t}
              <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
              {foreach $c_tab->getTabs() as $c_subtab}
                {if false == $c_subtab->isDisplayable() }
                  {continue}
                {/if}
                {assign "subactive" ""}
                {if $c_subtab->isActiveUrl()}
                  {assign "subactive" "active"}
                {/if}
                <li class="{$subactive}">
                  <a href="{$c_subtab->m_link}">{t}{$c_subtab->m_title}{/t}</a>
                </li>
              {/foreach}
            </ul>
          </li>
        {else}
          <li class="{$active}">
            <a href="{$c_tab->m_link}">{t}{$c_tab->m_title}{/t}</a>
          </li>
        {/if}
      {/foreach}
    </ul>

    <!-- <div class="clearfix visible-md"></div> -->

    {foreach $__menu->getWidgets() as $c_widget}
    <div class="nav navbar-nav navbar-right">
      {include file="{$c_widget.tpl}"}
    </div>
    {/foreach}
  </div>
</div>

