<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-navbar">
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    {assign "brand" $__menu->getBrand() }
    <a class="navbar-brand" href="{$brand.url}">{$brand.title}</a>
  </div>

  <div class="collapse navbar-collapse" id="menu-navbar">

    <ul class="nav navbar-nav">
      {foreach $__menu->getTabs() as $c_tab}

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
          <li>
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

    {foreach $__menu->getWidgets() as $c_widget}
    <div class="navbar-right">
      
    {include file="{$c_widget.tpl}"}
    </div>
    {/foreach}
  </div>
</div>

