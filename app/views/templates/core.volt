{{ get_doctype() }}
{% block openhtml %}<html{% if bodyclass is defined %} class="{{bodyclass}}"{% endif  %}>{% endblock %}
<head>
	<meta charset="UTF-8" />
	<title>{% block title %}API Dashboard{% endblock %}</title>
	<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
	{% block meta %}{% endblock %}
	<!-- bootstrap 3.0.2 -->
	{{ stylesheet_link('css/bootstrap.min.css') }}
	<!-- font Awesome -->
	{{ stylesheet_link('css/font-awesome.min.css') }}
	<!-- Ionicons -->
	{{ stylesheet_link('css/ionicons.min.css') }}
	<!-- Theme style -->
	{{ stylesheet_link('css/AdminLTE.css') }}

{% block stylesheets %}
	{{ assets.outputCss() }}
	{# { assets.outputStylesheet() } #}
{% endblock %}

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
</head>

{% block openbody %}
<body{% if bodyclass is defined %} class="{{bodyclass}}"{% endif  %}>{% endblock %}
{% autoescape true %}

{% block content %}
{{ content() }}
{% endblock %}


{% autoescape false %}
        <!-- jQuery 2.0.2 -->
        {{ javascript_include('js/jquery/jquery.min.js')}}
        <!-- Bootstrap -->
        {{ javascript_include('js/bootstrap.min.js')}}
{% endautoescape %}
{% block js_footer %}{{ assets.outputJs() }}{#{ assets.outputScript() }#}{% endblock %}
{% endautoescape %}
</body>
</html>
