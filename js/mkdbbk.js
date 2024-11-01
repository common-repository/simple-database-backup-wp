(function($){
	
	$(document).on('click','.mk_dbbk_button',function(ev){ 
		ev.preventDefault();
		
		$('.loader').show();
		
		$.ajax({
		url : admin_ajax.ajax_url,
		type : 'POST',
		data : {
			action : 'mkdbbk_mk_dbBackup',
			data : 'db_backup_request',			
		},
		success : function( response ) {
			console.log(response);
			$('.loader').hide();
			
			var res = JSON.parse(response);  
			if(res.status == 0){
				$('.notice_mkdbbk').html('<center><p>Database backup done. Location : /uploads/mk_dbbackup/'+res.filename+'</p></center>');
			}else{
				$('.notice_mkdbbk').html('<center><p style="color:red;">Error in database backup process!! Please try again. </p></center>');
			}

		setTimeout(function(){ location.reload(); }, 2000);
		
		}
		});
	});	
}
)(jQuery);