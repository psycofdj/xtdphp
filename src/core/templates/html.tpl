<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang}" lang="{$lang}">
  <head>
    <title>{$__title|default:''}</title>
    {if isset($__favicon)}
    <link rel="icon" type="image/png" href="{$__favicon}"/>
    {/if}
    <meta http-equiv="Content-Type" Content="text/html; charset=UTF-8"/>
    {foreach $__meta_http_equivs as $c_equiv => $c_content}
    <meta http-equiv="{$c_equiv}" content="{$c_content}"/>
    {/foreach}
    {foreach $__js_list as $c_js}
    <script type="text/javascript" src="{$c_js}"></script>
    {/foreach}
    {foreach $__css_list as $c_css}
    <link rel="stylesheet" type="text/css" href="{$c_css}" media="screen"/>
    {/foreach}
    <meta name="description" content="{$__meta_descr|default:''}" />
    <meta name="keywords" content="{$__meta_kw|default:''}"/>
  </head>
  <body {if isset($__onload) } onload="{$__onload}" {/if}>

    <script type="text/javascript">
      $("document").ready(function() {
         $("a").on("click", function() {
            var p = $(this).attr('href').split('?');
            if (p.length == 1)
              return true;
            var action = p[0];
            var params = p[1].split('&');
            var form = $(document.createElement('form')).attr('action', action);
            $('body').append(form);
            form.attr("method", "POST");
            for (var i in params) {
                var tmp= params[i].split('=');
                var key = tmp[0], value = tmp[1];
                $(document.createElement('input')).attr('type', 'hidden').attr('name', key).attr('value', value).appendTo(form);
            }
            $(form).submit();
            return false;
        });
      });
    </script>

    <div id="wrap">
      {include file="file:[core]menu.tpl"}
      {include file="$__content"}
    </div>
  </body>
</html>
