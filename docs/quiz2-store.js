/* Store results ----------------------------------------- */
/* store funcs ------------------------------------------- */
function truefalse() {
    let res = [];
    $(".truefalse .content").each((i, o) => {

        const question = $(o).find(".qtext").text();
        console.log(question);

        $(o).find(".answer>div").each((i, ansObj) => {
            const answer = $(ansObj).find("label").text();
            console.log("answer=" + answer);
            let correct = $(ansObj).find("i").attr("title");
            console.log("correct=" + correct);

            if (correct === "Correct") {
                correct = true;
            } else if (correct === "Incorrect") {
                correct = false;
            } else {
                return
            }
            res.push({
                question: question,
                answer: answer,
                correct: correct
            })
        });
    });
    return res;
}


function multichoice() {
    $(".answernumber").remove();

    let res = [];
    $(".multichoice .content").each((i, o) => {

        const question = $(o).find(".qtext").text();
        console.log(question);

        $(o).find(".answer>div").each((i, ansObj) => {
            const answer = $(ansObj).text();
            let correct = $(ansObj).find("i").attr("title");
            if (correct === "Correct") {
                correct = true;
            } else if (correct === "Incorrect") {
                correct = false;
            } else {
                return
            }
            res.push({
                question: question,
                answer: answer,
                correct: correct
            })
        })

    });
    return res;
}



function shortanswer(answerType) {
    $(".answernumber").remove();

    let res = [];
    $(answerType + " .content").each((i, o) => {

        const question = $(o).find(".qtext").text();
        console.log(question);

        $(o).find("input[type=text]").each((i, ansObj) => {
            const answer = $(ansObj).attr("value");
            console.log(answer);

            let correct = $(o).find("i").attr("title");
            console.log(correct);
            if (correct === "Correct") {
                correct = true;
            } else if (correct === "Incorrect") {
                correct = false;
            } else {
                return
            }
            console.log(correct);
            res.push({
                question: question,
                answer: answer,
                correct: correct
            })
        })

    });
    return res;
}


/* 未完成。新しいDB tableが必要 */
function multianswer() {
    $(".answernumber").remove();

    let res = [];
    $(".multianswer .content").each((i, o) => {

        const question = $(o).find("p").text();
        console.log(question);
        let answers = [];
        let corrects = [];
        $(o).find(".subquestion input").each((i, ansObj) => {
            const answer = $(ansObj).val();
            console.log(answer)
            correct = $(ansObj).find("i").attr("title");
            if (correct === "Correct") {
                corrects.push(true);
                answers.push(answer)
            } else if (correct === "Incorrect") {
                corrects.push(false);
                answers.push('')
            } else {
                corrects.push(null);
                answers.push('')
            }

        })
        res.push({
            question: question,
            answer: answers.join(","),
            correct: corrects.every(boolean => boolean === true)
        })

    });
    return res;
}


/* store main -------------------------------------- */

var name = 'localdb';
var version = '1.0';
var description = 'Web SQL Database';
var size = 2 * 1024 * 1024;
var db = openDatabase(name, version, description, size);

/* create table */
db.transaction(function(tx) {
    tx.executeSql("  create table if not exists quiz (     id integer primary key autoincrement,     question varchar,     answer varchar,     correct bool   ) ")
});


res = [...truefalse(), ...multichoice(), ...shortanswer(".shortanswer"), ...shortanswer(".numerical"), ...multianswer()];

function callback() {
    console.log("inserted.");
}
res.forEach(o => {
    console.log('insert into quiz (question, answer, correct) values (?, ?, ?)', [o.question, o.answer, o.correct]);
    db.transaction(function(tx) {
        tx.executeSql('insert into quiz (question, answer, correct) values (?, ?, ?)', [o.question, o.answer, o.correct], callback)
    })
});




/* Finish review ----------------------------------------- */
const exp = /http.*cmid=(\d+)/ig;
const url = document.URL.replace(exp, "https://my.uopeople.edu/mod/quiz/view.php?id=$1");
window.open(url);