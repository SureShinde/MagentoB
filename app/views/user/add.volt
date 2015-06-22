{% extends "templates/core_content.volt" %}
{% block content %}
<div class="row">
	<div class="col-xs-12">

		<div class="box box-primary">
			<div class="box-header">
				<h3 class="box-title">
					User Data
				</h3>
			</div>

			<form role="form" autocomplete="false" method="post">
				<div class="box-body">

					{% if error is defined %}
					<div class="alert alert-danger alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
						<b>Alert!</b> {{error}}
					</div>
					{% endif %}

					{% if success is defined %}
					<div class="alert alert-success alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
						<b>Alert!</b> {{success}}
					</div>
					{% endif %}


					<div class="form-group">
						<label for="name">Name</label>
						{{ text_field("name", "size": 32, "class":"form-control", "placeholder":"input name") }}
					</div>
					<div class="form-group">
						<label for="email">Email</label>
						{{ email_field("email", "size": 32, "class":"form-control", "placeholder":"email name") }}
					</div>
					{% if auth == 'administrator' %}
					<div class="form-group">
						<label for="org">Organisation</label>
						{{ select("appId", orgs, 'using': ['id', 'name'], 'class':'form-control' ) }}
					</div>
					<div class="form-group">
						<label for="level">Level</label>
						{{ select_static("level", ['administrator':'Administrator', 'superuser':'Superuser', 'user':'User'], 'class':'form-control') }}
					</div>
					{% endif %}
					{% if auth == 'superuser' %}
					<div class="form-group">
						<label for="level">Level</label>
						{{ selectStatic(["level", 'class':'form-control'], ['superuser':'Superuser', 'user':'User']) }}
					</div>
					{% endif %}
					<div class="form-group">
						<label for="password">Password</label>
						{{ password_field("password", "size": 32, "class":"form-control", "placeholder":"password", "value":"") }}
				</div>

				</div>
				<div class="box-footer">
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
			</form>

		</div>

	</div>
</div>

{% endblock %}