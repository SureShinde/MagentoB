{% extends "templates/core_content.volt" %}
{% block content %}
                    <div class="row">

                        <div class="col-xs-12">

                            <div class="box">
                                <div class="box-body table-responsive">
                                	<form>
                                    <div class="filtering" id="simplesearchdiv">
        								Search : <input type="text" name="qsearch" id="qsearch" />
        								<button type="submit" id="searchbutton">Search</button>
										[<a href='#' id='resetsimplesearch'>Show All</a>]
										[<a href='#' id='showadvancedsearch'>Advanced Search</a>]
									</div>
									</form>
									<div class="filtering" id="advancedsearchdiv" style="display:none;">

										<table>
											<tr>
												<td width="40%">Name</td>
												<td width="20%">contains</td>
												<td width="40%"><input type="text" name="name" value="" size="30"></td>
											</tr>
											<tr>
												<td width="40%">Website</td>
												<td width="20%">contains</td>
												<td width="40%"><input type="text" name="website" value="" size="30"></td>
											</tr>
											<tr>
												<td width="40%">Email</td>
												<td width="20%">contains</td>
												<td width="40%"><input type="text" name="email" value="" size="30"></td>
											</tr>
											<tr>
												<td width="40%"></td>
												<td width="20%"></td>
												<td width="40%"><br>
													<button type="submit" id="advancedsearchbutton">Search</button><br>
													[<a href='#' id='resetadvancedsearch'>Reset</a>]
													[<a href='#' id='showsimplesearch'>Simple Search</a>]
												</td>
											</tr>
										</table>

									</div>
                                    <div id="datalistcustomer"></div>
									<div class="pull-right"><a href="/dashboard/user/add">+ Add New User</a></div>
									<div class="clear clearfix"></div>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>
{% do assets.addJs("/ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js") %}
{% do assets.addCss("css/jQueryUI/jquery-ui-1.8.16.custom.css") %}
{% do assets.addJs("js/plugins/jtable/jquery.jtable.min.js") %}
{% do assets.addCss("js/plugins/jtable/themes/metro/blue/jtable.min.css") %}
<!-- DATA TABLES SCRIPT -->
{% do assets.addScript("

$(function() {

	$('#datalistcustomer').jtable({
        title: '" ~ subtitletable ~ "',
        paging: true,
        pageSize: 30,//default value for pagesize
        pageSizes: [30,60,120,240],
        sorting: true,
        multiSorting: true,
        defaultSorting: 'Name ASC',
        selecting: true, //Enable selecting
        //multiselect: true, //Allow multiple selecting
        //selectingCheckboxes: true, //Show checkboxes on first column
        //selectOnRowClick: false, //Enable this to only select using checkboxes
        actions: {
            listAction: '" ~ url ~ "',
			deleteAction: '/dashboard/user/delete',
        },
        fields: {
            id: {
                key: true,
                title: 'Id',
                create: false,
                edit: false,
                list: true,
                width: '5%'
            },
            name: {
                title: 'Name',
                width: '23%',
            },
            appname: {
                title: 'Org.Name',
                sorting: false,
                list: true,
            },
            level: {
                title: 'Level',
                sorting: false,
                list: true,
            },
			CustomAction: {
				title: '',
				width: '1%',
				sorting: false,
				create: false,
				edit: false,
				list: true,
				display: function (data) {
					if (data.record) {
						"~ "return '"~ '<a title="Edit" onclick="location.href ='~'\\'~"'user/edit?id=' + data.record.id + '"~'\\'~"';return false"~'; "><i class="fa fa-file-text-o"></i></a>'~"';" ~"
					}
				}
			}

/*
            createdDate: {
                title: 'Member Since',
                width: '15%',
                type: 'date',
                display: function (data) {
                    return moment(data.record.createdDate).format('DD/MM/YYYY HH:mm:ss');
                }
            },
            lastUpdatedDate: {
                title: 'Last Updated',
                width: '15%',
                type: 'date',
                display: function (data) {
                    return moment(data.record.lastUpdatedDate).format('DD/MM/YYYY HH:mm:ss');
                }
            }*/
        }
    });

    $('#datalistcustomer').jtable('load');
    $('#searchbutton').click(function (e) {
        e.preventDefault();
        $('#datalistcustomer').jtable('load', {
            qsearch: $('#qsearch').val()
        });
    });

    $('#datalistcustomer').click();

    $('#resetsimplesearch' ).click(function() {
    	$('#datalistcustomer').jtable('load');
    	$('#qsearch').val('');
    });
    $( '#showadvancedsearch' ).click(function() {
    	$('#advancedsearchdiv').show('slow');
    	$('#simplesearchdiv').hide('slow');
    });
    $( '#showsimplesearch' ).click(function() {
    	$('#advancedsearchdiv').hide('slow');
    	$('#simplesearchdiv').show('slow');
    });
    $('#date1, #date2, #date3, #date4').datepicker({
		format: 'mm/dd/yyyy'
	});
	$('#advancedsearchbutton').click(function (e) {
        //e.preventDefault();
        //alert('still in dummy.. waiting adjusment');
        e.preventDefault();
		$('#datalistcustomer').jtable('load', {
			name: $('#advancedsearchdiv [name=name]').val(),
			websites: $('#advancedsearchdiv [name=website]').val(),
			email: $('#advancedsearchdiv [name=email]').val(),
		});
    });


});


") %}

{% endblock %}