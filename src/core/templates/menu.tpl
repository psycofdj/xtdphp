<div class="navbar navbar-inverse navbar-fixed-top">

  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-navbar">
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="{$__menu_brand.url}">{$__menu_brand.title}</a>
  </div>

  <div class="collapse navbar-collapse" id="menu-navbar">

  <!-- <p class="navbar-text pull-right"><i class="icon-user icon-white"></i> -->
  <!--   xavier@marcelet.com  |  <a href="connexion.php?c=logout" class="navbar-link">Déconnexion</a> -->
  <!-- </p> -->
  <!-- <ul class="nav pull-right" id="history"> -->
  <!--   <li id="fat-menu" class="dropdown"> -->
  <!--     <a href="#" id="histo" role="button" class="dropdown-toggle" data-toggle="dropdown">Activité<b class="caret"></b></a> -->
  <!--     <ul class="dropdown-menu" role="menu" id="histo-content" aria-labelledby="drop3"> -->
  <!--     </ul> -->
  <!--   </li> -->
  <!-- </ul> -->

  <ul class="nav navbar-nav">
    {foreach $__menu as $c_menu}
    {assign "active" ""}

    {if array_key_exists("sections", $c_menu)}

    {* check if one of suburls is current url *}
    {foreach $c_menu.sections as $c_section}
    {if $smarty.server.PHP_SELF == $c_section.link} {assign "active" "active"} {/if}
    {/foreach}

    <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        {t}{$c_menu.title}{/t}
        <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        {foreach $c_menu.sections as $c_section}
        <li>
          <a href="{$c_section.link}" title="{$c_section.role}">{t}{$c_section.title}{/t}</a>
        </li>
        {/foreach}
      </ul>
    </li>
    {else}
    {if $smarty.server.PHP_SELF == $c_menu.link} {assign "active" "active"} {/if}
    <li class="{$active}">
      <a href="{$c_menu.link}" title="{$c_menu.role}">{t}{$c_menu.title}{/t}</a>
    </li>
    {/if}
    {/foreach}
  </ul>
</div>
</div>
