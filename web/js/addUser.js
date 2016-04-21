window.onload = function(){
	$("#form_student").attr('onclick', 'isStudent();');
	isStudent();
}

function isStudent(){
		
	if($("#form_student").prop('checked')){
		$("#form_supervisor").prop('disabled', false);
		$("#form_supervisor").attr('required', ''); 
			}
	else{
		$("#form_supervisor").prop('disabled', true);
		$("#form_supervisor").removeAttr('required');
	}
	
}