$(document).ready(function() {
        $(".showlist").tableSorter();
        $("#showallitem").click(function(){
                $("#newitem").slideDown();
                $("#existing").html("");
		$(this).hide();
        });
        $("#shownewitem").click(function(){
                $("#newitem").slideDown();
                $("#existing").html("");
		$(this).hide();
        });
});
