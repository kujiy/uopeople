$("select[id^=id_grade]").each(function(e) { $(this).val(10);});

$("fieldset").each(function(e){ 
    $this = $(this);
    var textarea = $this.find("textarea");

    $this.find("div[id^=id_dim_] p").each(function(e){ 
        var id = $(this).attr('id');
        
        // console.log("textarea="+textarea);
        var t = $(this).text();
        var ptn1 = new RegExp(/^.*?Requirements /);
        var ptn2 = new RegExp(/\s?(Did |Is |Are |Does )the /);
        var s = t.replace(ptn1, "").replace(ptn2, "This ").replace("essay at least", "essay is at least");
        var s = replace_past_tense(s, textarea);
        // console.log(s);
    });
});

var message ="This student provided a great work on this difficult subject. The essay was well-written and easy to read. The explanations deepened my understandings. Thank you for your work. Good job!";
$("#id_feedbackauthor_editoreditable").text(message);
$("#id_feedbackauthor_editor").val(message);

function replace_past_tense(s, textarea) {
    // "This student refer" の "refer" を取得
    var verb = get_word(s,3);
    verb = String(verb).replace(",", "");
    // console.log("verb="+verb);
    // referを 過去形 referred に変換
    var past = get_past_tense(verb, s, textarea);
}

// get n-th word
function get_word(s,n){
    return s.split(/\s+/).slice(n-1,n);
}

function get_past_tense(verb, s, textarea) {
    var url = "https://tensify.herokuapp.com/" + verb;

    $.ajax({
        async : false,
        type: "GET",
        url: url,
        dataType: "text"
    }).done(function(past) {
        // console.log("res="+past);
        update_textarea(s, verb, past, textarea);
    });
}

function update_textarea(s, verb, past, textarea) {

    // console.log("past="+past);

    var ptn = new RegExp("(.*?)" + verb + "(.*)");
    out = s.replace(ptn, "$1" + past + "$2").replace("?", ".");
    console.log(out);
    $(textarea).val(out);
}
