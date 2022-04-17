
function chooseCorrect(question, answer, ansObj, hasChoosenCorrect) {
	db.transaction(function (tx) {
	  tx.executeSql('select correct from quiz where question like ? and answer like ?', [question, answer],
	    (tx, results) => {
	      /*
	       if(!hasChoosenCorrect && results.rows.length == 0) {
            	$(ansObj).find("input:radio").prop("checked",true);
            	return;
	      }
	      */

	      /* SUCCESS */
          for (i = 0; i < results.rows.length; i++){
          	let item = results.rows.item(i);
	        if(item.correct === "true") {
            	$(ansObj).find("input:radio").prop("checked",true);
            	hasChoosenCorrect = true;
            	return hasChoosenCorrect
	        } else if (item.correct === "false") {
	        	/* skip */
            	$(ansObj).find("input:radio").attr("mymark", "incorrect");
	        } else if (!hasChoosenCorrect && item.correct === "null") {
	            /* correct=null */
            	$(ansObj).find("input:radio").prop("checked",true);
	        }
	      }
	    },
	    function(err){
	      /* ERROR */
	        console.log(err);
			return false;
	    })
	  }
	)
}


function truefalse() {
  let res = [];
  $(".truefalse .content").each((i, o) => {


    const question =  $(o).find(".qtext p").html();

    $(o).find(".answer>div").each((i, ansObj) => {
    	let hasChoosenCorrect = false;
         const answer = $(ansObj).find("label").html();
         hasChoosenCorrect = chooseCorrect(question, answer, ansObj, hasChoosenCorrect);
         if(!hasChoosenCorrect) {
        	const probability = $(ansObj).find("input:radio").attr("mymark") !== "incorrect";
        	if (probability) {
        	     $(ansObj).find("input:radio").prop("checked",true);
        	}
         }
    })
  });
  return res;
}


function multichoice() {
  let res = [];
  $(".multichoice .content").each((i, o) => {

    const question =  $(o).find(".qtext p").html();

    $(o).find(".answer>div").each((i, ansObj) => {
    	let hasChoosenCorrect = false;
         const answer = $(ansObj).find("p").html();
         hasChoosenCorrect = chooseCorrect(question, answer, ansObj, hasChoosenCorrect);
         if($(ansObj).find("input:radio").prop("checked") === false) {
        	const probability = $(ansObj).find("input:radio").attr("mymark") !== "incorrect";
        	if (probability) {
        	     $(ansObj).find("input:radio").prop("checked",true);
        	}
         }
    })
  });
  return res;
}


/* ------------------------------------- */

var name = 'localdb';
var version = '1.0';
var description = 'Web SQL Database';
var size = 2 * 1024 * 1024;
var db = openDatabase(name, version, description, size);

  /* create table */
  db.transaction(function(tx){
    tx.executeSql("  create table if not exists quiz (     id integer primary key autoincrement,     question varchar,     answer varchar,     correct bool   ) ")
  });


res = [...truefalse(), ...multichoice()];


$("html, body").animate({
scrollTop: $(document).height()
}, "slow");
$("form .submitbtns input").click();






