 <div class="row">
	<div id="breadcrumb" class="col-md-12">
		<ol class="breadcrumb">
			<li><a href="dashboard">Dashboard</a></li>
			<li><a href="#">{{$subheading1}}</a></li>
			<li><a href="#">{{$subheading2}}</a></li>
			<li><a href="#">{{$subheading3}}</a></li>
		</ol>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-12">
		<div class="box">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-pencil"></i>
					<span>Create a New Contract</span>
				</div>
				<div class="box-icons">
					<a class="collapse-link">
						<i class="fa fa-chevron-up"></i>
					</a>
					<a class="expand-link">
						<i class="fa fa-expand"></i>
					</a>
					<a class="close-link">
						<i class="fa fa-times"></i>
					</a>
				</div>
				<div class="no-move"></div>
			</div>
			<div class="box-content">
				<h4 class="page-header">Add people who need to sign this contact</h4>
				<form class="form-horizontal" role="form" action="signeerecord" method="post" id="form-add-signees">
					<div class="form-group">
						<div class="col-sm-10">
								<input type="text" class="form-control" name="signee_email" id="signee_email"/>
								<button type="submit" class="btn btn-primary btn-label-left btn-block">
								<span><i class="fa fa-save"></i></span>
									Add Signee
								</button>
						</div>
					</div>
					<div class="form-group">
						<!-- The container for the uploaded files -->
					</div>
					<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
					<input type="hidden" name="contract_id" value="{{$contract_id}}">
				</form>
				<div id="added_signees" class="added_signees"></div>
				<br>
				<div class="next button">
					<div class="col-sm-2">
						<button id="btnNext" class="btn btn-primary btn-label-left btn-block">
						<span><i class="fa fa-save"></i></span>
							Save and Proceed
						</button>
					</div>
				</div>
				<br><br>
			</div>
		</div>
	</div>
</div>

<script>
$(function () {
    $('#form-add-signees').on('submit', function(){ 
                 
       // ajax post method to pass form data to the 
        $.post(
            $(this).prop('action'),
            {
                "_token": $( this ).find( 'input[name=_token]' ).val(),
                "contract_id": $( this ).find( 'input[name=contract_id]' ).val(),
                "email": $( this ).find( 'input[name=signee_email]' ).val()
            },
            function(data){
            	if(data['exists']==1) {
					$('<p/>').text('Signee '+data['name']+data['message']+data['email']+')').appendTo('#added_signees');
				}
				else if(data['exists']==0) {
					$('<p/>').text(data['message']+data['email']).appendTo('#added_signees');
				}
            },
            'json'
        ); 
       
        return false;
	});
	//next page
    $("#btnNext").click(function(){
    	var ajax_url = 'sign/' + '{{$contract_id}}';
		LoadAjaxContent(ajax_url);
    });
});
</script>