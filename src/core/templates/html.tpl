<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang}" lang="{$lang}">
  <head>
    <title>{$__title}</title>
    <meta http-equiv="Content-Type" Content="text/html; charset=UTF-8">
{foreach $__meta_http_equivs as $c_equiv => $c_content}
    <meta http-equiv="{$c_equiv}" content="{$c_content}"/>
{/foreach}
{foreach $__js_list as $c_js}
    <script type="text/javascript" src="{$c_js}"></script>
{/foreach}
{foreach $__css_list as $c_css}
    <link rel="stylesheet" type="text/css" href="{$c_css}"/>
{/foreach}
{if isset($__meta_descr)}
    <meta name="description" content="{$__meta_descr}" />
{/if}
{if isset($__meta_kw)}
    <meta name="keywords" content="{$__meta_kw}">
{/if}
  </head>
  <body {if isset($__onload) } onload="{$__onload}" {/if}>
    {include file="file:[app]$__content"}
  </body>
</html>
