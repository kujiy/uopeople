function isGradedQuiz() {
    let out = false;
    $("#page-navbar nav a").each((_i, o) => {
        if ($(o).text().includes("Graded Quiz") || ($(o).text().includes("Final Exam") && !$(o).text().includes("Review Quiz")))
            out = true;
    });
    return out;
}
var GRADED_QUIZ = isGradedQuiz();
console.log("Graded quiz = " + GRADED_QUIZ);
if (GRADED_QUIZ) {
    console.log("GRADDDDDDDDDDDDDDDDDDDDDDDDDDDDDD");
}

function chooseCorrect(question, answer, ansObj) {
    db.transaction(function(tx) {
        let sql = "";
        let arg = [];
        if (answer) {
            sql = 'select * from quiz where question like ? and answer like ?';
            arg = [question, answer];
        } else {
            sql = 'select * from quiz where question like ? ';
            arg = [question];
        }
        console.log(sql, arg);
        tx.executeSql(sql, arg,
            (tx, results) => {
                console.log("result:");
                console.log(results);
                console.log("--- results.rows.length: " + results.rows.length);

                if (results.rows.length === 0) {
                	console.log("--- results.rows.length : got in");
                    /* check when no answer has been recorded */
                    tx.executeSql('select correct from quiz where question like ?', [question],
                        (tx, results) => {
                		console.log("--- results.rows.length - inside: " + results.rows.length);
                            if (results.rows.length === 0) {
                                console.log("--- because no-DB: " + answer);
                                if (!GRADED_QUIZ) {
                                	console.log("checked: " + answer);
                                    $(ansObj).find("input:radio").prop("checked", true);
                                }
                            }
                        });
                }
                /* SUCCESS */
                let hasChoosenCorrect = false;
                for (i = 0; i < results.rows.length; i++) {
                    let item = results.rows.item(i);
                    console.log(item.question + " anwser=" + item.answer + " DB=" + item.correct);
                    if (item.correct === "true") {
                        console.log("--- because DB has True. the correct answer from DB is " + item.answer);
                        hasChoosenCorrect = true;
                        $(ansObj).find("input:radio").prop("checked", true);
                        $(ansObj).val(item.answer); // shortanswer
                    } else if (item.correct === "false") {
                        /* skip */
                        $(ansObj).find("input:radio").attr("mymark", "incorrect");
                    }
                }
                if (!hasChoosenCorrect) {
                	console.log($(ansObj).find("input:radio").attr("mymark"))
                    const probability = $(ansObj).find("input:radio").attr("mymark") !== "incorrect";
                    if (probability) {
                        console.log("--- because probability: " + answer);
                        $(ansObj).find("input:radio").prop("checked", true);
                    } else {
                    	console.log("--- no probablitiy")
                    }
                }

            },
            function(err) {
                /* ERROR */
                console.log(err);
                return false;
            })
    })
}


async function truefalse() {
    $(".truefalse .content").each((i, o) => {

        const question = $(o).find(".qtext").text();
        console.log(question);
        $(o).find(".answer>div").each((i, ansObj) => {
            const answer = $(ansObj).find("label").text();
            console.log(answer);
            chooseCorrect(question, answer, ansObj);
        })
    });
}


async function multichoice() {
    $(".answernumber").remove();

    $(".multichoice .content").each((i, o) => {

        const question = $(o).find(".qtext").text();

        $(o).find(".answer>div").each((i, ansObj) => {
            const answer = $(ansObj).text();
            console.log(answer);
            chooseCorrect(question, answer, ansObj);
        })
    });
}

async function shortanswer(answerType) {
    $(".answernumber").remove();

    $(answerType + " .content").each((i, o) => {

        const question = $(o).find(".qtext").text();
        console.log(question);

        $(o).find("input[type=text]").each((i, ansObj) => {
            chooseCorrect(question, null, ansObj);
        })
    });
}


/* ------------------------------------- */

var name = 'localdb';
var version = '1.0';
var description = 'Web SQL Database';
var size = 2 * 1024 * 1024;
var db = openDatabase(name, version, description, size);

/* create table */
db.transaction(function(tx) {
    tx.executeSql("  create table if not exists quiz (     id integer primary key autoincrement,     question varchar,     answer varchar,     correct bool   ) ")
});


res = Promise.all([truefalse(), multichoice(), shortanswer(".shortanswer"), shortanswer(".numerical")]).then(() => {

	/* -----------------------------------*/
	setTimeout(function() {
        $(document).scrollTop($(document).height());
	    $("form .submitbtns input[name=next]").click();
	}, 2000);




})


