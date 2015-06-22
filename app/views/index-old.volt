{% extends "templates/core.volt" %}

{% block title %}{% if title is defined %}{{title}}{% else  %}API Dashboard{% endif  %}{% endblock %}
{% block stylesheets %}
	{{ stylesheet_link('css/custom.css') }}
	{{ assets.outputCss() }}
	{#{ assets.outputStylesheet() }#}
{% endblock %}

{% block js_footer %}
        <!-- AdminLTE App -->
        {{ javascript_include('js/AdminLTE/app.js')}}

{{ assets.outputJs() }}{#{ assets.outputScript() }#}
{% endblock %}