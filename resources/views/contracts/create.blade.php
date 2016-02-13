<div class="row">
	<div id="breadcrumb" class="col-md-12">
		<ol class="breadcrumb">
			<li><a href="dashboard">Dashboard</a></li>
			<li><a href="#">{{$type['parent']}}</a></li>
			<li><a href="#">New {{$type['name']}}</a></li>
		</ol>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-12">
		<div class="box">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-pencil"></i>
					<span>Create new {{$type['name']}}</span>
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
				<h4 class="page-header">New {{$type['name']}}</h4>
				<form class="form-horizontal" role="form" method="post" action="{{$posturl}}" id="form-add-a-contract">
					<div class="form-group">
						<label class="col-sm-2 control-label" for="contract_title">Contract Title:</label>
						<div class="col-sm-10">
								<input type="text" @if(isset($data)) value="{{$data['title']}}" @endif class="form-control" id="contract_title"/>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="form-group">

						<!--<label class="col-sm-2 control-label" for="form-styles">Textarea</label>-->
						<div class="col-sm-12">
								<textarea class="form-control" rows="15" name="contract_content" id="wysiwig_simple">@if(isset($data)) {{$data['content']}} @endif</textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-2">
							<button type="submit" class="btn btn-primary btn-label-left btn-block">
							<span><i class="fa fa-save"></i></span>
								Save and Proceed
							</button>
						</div>
					</div>
					<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
					<input type="hidden" name="contract_type" value="{{$type['id']}}">
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

$(document).ready(function() {
	// Create Wysiwig editor for textarea
	TinyMCEStart('#wysiwig_simple');
	tinymce.execCommand('mceRemoveEditor',true,wysiwig_simple);
	tinymce.execCommand('mceAddEditor',true,wysiwig_simple);

	    $('#form-add-a-contract').on('submit', function(){ 
                 
       // ajax post method to pass form data to the 
		        $.post(
		            $(this).prop('action'),
		            {
		                "_token": $( this ).find( 'input[name=_token]' ).val(),
		                "contract_type": $( this ).find( 'input[name=contract_type]' ).val(),
		                "contract_title": $( '#contract_title' ).val(),
		                "contract_content": tinyMCE.activeEditor.getContent()
		            },
		            function(data){
		            	var ajax_url = 'file/' + data["contract_id"];
		            	window.location.hash = ajax_url;
		                LoadAjaxContent(ajax_url);
		            },
		            'json'
		        ); 
		       
		        return false;
		    }); 
});
//
// Helper for run TinyMCE editor with textarea's
//
function TinyMCEStart(elem){
	var plugins = [];
	tinymce.init({selector: elem,
		theme: "modern",
		plugins: plugins,
		//content_css: "css/style.css",
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
		style_formats: [
			{title: 'Header 2', block: 'h2', classes: 'page-header'},
			{title: 'Header 3', block: 'h3', classes: 'page-header'},
			{title: 'Header 4', block: 'h4', classes: 'page-header'},
			{title: 'Header 5', block: 'h5', classes: 'page-header'},
			{title: 'Header 6', block: 'h6', classes: 'page-header'},
			{title: 'Bold text', inline: 'b'},
			{title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
			{title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
			{title: 'Example 1', inline: 'span', classes: 'example1'},
			{title: 'Example 2', inline: 'span', classes: 'example2'},
			{title: 'Table styles'},
			{title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
		]
	});
}
</script>						