                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        {{subtitle}}
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>

						{% for name, link in breadcrumbs %}

							{% if link and (not(link is scalar) or link|trim != '#') %}
						<li>
							{{ link_to(link,name) }}
							{% else %}
						<li class="active">
							{{ name }}
							{% endif %}

						</li>

						{% endfor %}
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
				{% block content %}
					{{ content() }}
				{% endblock %}

                </section><!-- /.content -->

{% block scripts %}
{% endblock %}

