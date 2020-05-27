<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://code.jquery.com/jquery-3.1.1.slim.js" integrity="sha256-5i/mQ300M779N2OVDrl16lbohwXNUdzL/R2aVUXyXWA=" crossorigin="anonymous"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://clipboardjs.com/bower_components/primer-css/css/primer.css">
    <script src="https://cdn.jsdelivr.net/clipboard.js/1.6.0/clipboard.min.js"></script>

</head>
<body>

<?php

error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED | E_STRICT));

date_default_timezone_set("Asia/Tokyo");

if ($_GET["url"] != "") {
    // 複数行もありえるurl
    $multipleUrl =  filter_input(INPUT_GET, "url");
    $aUrl = explode("\n", $multipleUrl);
//print_r($aUrl);

    $out = "";
    $outshort = "";

    // 1urlずつ
    foreach ($aUrl as $url) {
        // 改行だけのurlをスキップ
        if (!$url || $url == "" || $url == "¥n" || $url == "¥r" || ord($url) == "13") {
            continue;
        }
//        echo "<br>¥n";
    //echo $url;
    //    echo "<br>¥n";

        $url = trim($url);
        $urlp = "http://" . $url ;
        $urls = "https://" .  $url ;

        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            $url = $url;
        } elseif (!filter_var($urlp, FILTER_VALIDATE_URL) === false) {
            $url = $urlp;
        } elseif (!filter_var($urls, FILTER_VALIDATE_URL) === false) {
            $url = $urls;
        } else {
            echo "your url is invalid. $url";
        }
        $today = date("M d, Y");

        $title = get_title($url) ;
        // print_r($title);
        // exit;

        // 年と作者
        $author =  filter_input(INPUT_GET, "author");
        $rawauthor =  filter_input(INPUT_GET, "rawauthor");
        // bookmarkletで選択していた部分をmixed(date+author)として処理
        $mixed =  filter_input(INPUT_GET, "sel");
        $year =  get_year(filter_input(INPUT_GET, "year"), $mixed);

        // filter_input(INPUT_GET, "email", FILTER_VALIDATE_EMAIL)
        if ($url && $title) {
            $out="\n" ;
            if ($rawauthor || $author || $mixed) {

                // mixedからコピペでauthorを手動取り出しするシーンを考慮してmixed以外を優先してauthor取得
                if ($rawauthor) {
                    $outAuthor = $rawauthor;
                } elseif ($author) {
                    $outAuthor = get_initial($author);
                } elseif ($mixed) {
                    $outAuthor = get_author_from_mixed($mixed);
                }
                $out .="$outAuthor. ";
                if ($year) {
                    $out .="($year). ";
                } else {
                    $out .="(n.d.). ";
                }
                // https://stackoverflow.com/questions/1070244/how-to-determine-the-first-and-last-iteration-in-a-foreach-loop
            }
            $out .= "$title. Retrieved on $today, from $url";
            $outshort .= "($outAuthor, $year)";
        }
        $out = preg_replace("/¥t+/", "", $out);
        $out = trim($out);
    }
}
function get_title($url)
{
    try {
        $str = getUrlContent($url);

        if ($str === false) {
            // Handle the error
            //echo 1; echo " $url"; exit;
            return false;
        }
    } catch (Exception $e) {
        // Handle exception
              echo 21;
        exit;
    }
    //$url = "https://www.brookings.edu/testimonies/ten-years-later-the-status-of-the-u-n-human-rights-council/";

    if (strlen($str)>0) {
        $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
        preg_match("/\<title\>(.*?)\<\/title\>/i", $str, $title); // ignore case
        //print_r($title);exit;
        // タイトルがとれない場合はファイル名
        if ($title[1] == "") {
            // 拡張子があったらファイル名をタイトルとする
            preg_match("/^.*\.(jpg|jpeg|png|gif|docx?|xlsx?|pdf|txt|md).*$/i", $url, $ext); // ignore case
            //print_r($ext);exit;

            if ($ext[1]) {
                $title[1] = basename($url);
                //     print_r($title);exit;
            } else {
                $title[1] = $url;
            }
        } elseif (preg_match("@access denied@i", $title[1])) {
            // access deniedとか言うコンテンツが返ってきた場合(IMF.orgなど)
            $title[1] = "********************" . $title[1] . "******************";
        }

        return $title[1];
    }
}


function getUrlContent($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    // $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_exec($ch) === false) {
        // echo 'Curl error: ' . curl_error($ch);
        // print_r($data);
        curl_close($ch);
        return false;
    } else {
        // echo 'Operation completed without any errors';
        curl_close($ch);
        return $data;
    }

    // $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);
    // return ($httpcode>=200 && $httpcode<300) ? $data : false;
}

function get_initial($str)
{
    $str = trim($str);

    // 名前に空白がない first or given nameしかない場合はそのまま返す
    if (!preg_match("@\s@", $str)) {
        return ucwords(strtolower($str));
    }
    // 名字と名前に分解
    $words = explode(" ", $str);
    $acronym = "";

    $last = array_pop($words);
    foreach ($words as $w) {
        $acronym .= " " . $w[0];
    }
    $res = "$last,$acronym";
    $res = preg_replace("/¥s+/", " ", $res);
    return ucwords(strtolower($res));
}

// author/data mixed から自動でそれっぽいのを抽出
function get_author_from_mixed($str)
{
    $str = trim($str);
    // echo "$str";
    // exit;

    // 複数行来た場合、1行目だけ採用
    // TODO: 2行目以降も見て、"By " があればその行がauthorだとして処理
    $str = strtok($str, "\n");

    // 指定の文字列を消す
    // 月、数字、記号
    // TODO: Mayさんとかの名前が消えてしまう
    $ptn = "/(January |February |March |April |May |June |July |August |September |October |November |December |Jan |Feb |Mar |Apr |May |Jun |Jul |Aug |Sep |Oct |Nov |Dec |by |\d+|[\$-\/:-?{-~!\"\^_`\[\]])/i";
    $str = preg_replace($ptn, "", $str);
    $str = trim($str);

    if ($str != "") {
        return get_initial($str);
    } else {
        return false;
    }
}

// 文字列からyearぽいものを返す
// ２つの候補から順に探す
function get_year($str, $mixed)
{
    if (preg_match("/(\d\d\d\d)/", $str, $res)) {
        return $res[1];
    } elseif (preg_match("/(\d\d\d\d)/", $mixed, $res)) {
        return $res[1];
    } else {
        return "n.d.";
    }
}
?>



    <!-- Begin page content -->
    <div class="container">
      <div class="page-header">
        <h1>APA format maker</h1>
      </div>
      <p class="lead"> Cite your website in APA format for free. </p>
        <form action="./apa.php" method="get" >

        <label for="basic-url">Input your reference url:</label>

                <div class="form-group form-group-lg">

        <!-- URL入力 -->
        <div class="input-group">
          <span class="input-group-addon">http://</span>
          <textarea class="form-control" id="url" name="url"  placeholder="http://example.com&#10;http://w3c.org"><?php echo $url; ?></textarea>
        </div><br />


        <!-- 年と著者 -->
        <div class="input-group">
            <div class="form-group row">

                <label for="author">Author(Given name Family name)</label>
                <input type="text" class="form-control" id="author" name="author"  value="<?php echo $author; ?>" placeholder="Tiger McLean"></input>

            </div>
      <div class="form-group row">
          <label for="rawauthor">Raw Author(Family name, First letter of the Given name)</label>
          <input type="text" class="form-control" id="rawauthor" name="rawauthor"  value="<?php echo $rawauthor; ?>" placeholder=" McLean, T"></input>
      </div>
      <!-- まぜこぜ year/author -->
      <div class="form-group row">
          <label for="sel">Mixed Author and dates(Automatic extraction)</label>
          <input type="text" class="form-control" id="sel" name="sel"  value="<?php echo $mixed; ?>" placeholder=" McLean, T"></input>
      </div>

            <div class="form-group row">
                <label for="year">Year</label>
                <input type="text" class="form-control" id="year" name="year"  value="<?php echo $year; ?>" placeholder="2017"></input>
            </div>
        </div>


                <button type="submit" class="btn btn-default btn-lg btn-block">Get APA</button>

        </div>

        </form>

                <button onclick="removeDecoration();" class="btn btn-default btn-lg btn-block">Remove font-size;</button>

        <br>

        <div class="panel panel-success hidden">
          <div class="panel-heading">Here you are.</div>
          <div class="panel-body" id="ans">
          <!-- Target -->
                    <form class="form-horizontal form-group-lg">
                          <div class="form-group">

                                <textarea id="bar" class="form-control" rows="2"><?php echo $out; ?></textarea>

                                <!-- Trigger -->
                                <button class="btn btn-block" data-clipboard-target="#bar">
                                   <span class="glyphicon glyphicon-copy" aria-hidden="true"></span>copy to clipboard</button>

                                <br />

                                <textarea id="short" class="form-control" rows="2"><?php echo $outshort; ?></textarea>

                                <!-- Trigger -->
                                <button class="btn btn-block" data-clipboard-target="#short">
                                   <span class="glyphicon glyphicon-copy" aria-hidden="true"></span>copy to clipboard</button>

                                <br />

                                <div class="alert alert-warning" role="alert">Note: This tool doesn't take Author's name.</div>

                            </div>
                    </form>


          </div>
        </div>

        <div class="panel panel-danger hidden">
          <div class="panel-heading">Error</div>
          <div class="panel-body" id="ans">
            Your url is invalid. Please check the url is correct and alive.
          </div>
        </div>

        <!-- 履歴 -->
        <div id="lastResults"></div>
    </div>





          <textarea class="form-control" id="convert" name="convert"  value="" placeholder="text"></textarea>



  </body>
</html>

<script>
$(function() {
    console.log( "ready!" );

    $("#url").focus().select();
    <?php
    if ($url && !$title) {
        echo <<<TTT
        hideError();
TTT;
    }
    if ($out) {
        echo <<<TTT
        showSuccess();
TTT;

        // GET APAした後のアクセスだったらコピーしやすいようにfocusを当てる
        echo <<<TTT
$("#bar").focus().select();
TTT;
    }
     ?>

     // click then clear input
//     $("input[type=text]").click(function() {
  //    $(this).closest('form').find("input[type=text], textarea").select();
  //   });

    // clipboard
    new Clipboard('.btn');
});

function hideError() {
        $(".panel-danger").removeClass("hidden");
}

function showSuccess() {

        $(".panel-danger").addClass("hidden");
        $(".panel-success").removeClass("hidden");
}


function removeDecoration() {

    console.log("remove");
    var str = $("#url").val();
    console.log(str);
    str2 =str.replace(new RegExp(/font-size:[0-9.]{5}px;/, 'g'), "");
    $("#bar").val(str2).css("height", "500");

    // らコピーしやすいようにfocusを当てる
    $("#bar").focus().select();
    // clipboard
    new Clipboard('.btn');
     showSuccess();
    return false;

}









// 履歴保持
if (typeof(Storage) !== "undefined") {
    // Code for localStorage/sessionStorage.


           function addHistory(url, apa) {
               console.log("addHistory");
               //Storing New result in previous History localstorage
               if (localStorage.getItem("history") != null)
               {
                   var historyTmp = localStorage.getItem("history");
                   historyTmp += url + "|";
                   localStorage.setItem("history",historyTmp);
               }
               else
               {
                   var historyTmp = url + "|";
                   localStorage.setItem("history",historyTmp);
               }
            }
            <?php

            if ($url && $out) {
                $aOut = explode("\n", $out);
                // 1urlずつ
                $k=0;
//                print_r($aOut);
                foreach ($aOut as $o) {
                    // 改行だけのurlをスキップ
                    //echo  ord($o) ;exit;
                    if (!$o || $o == "" || $o == "¥n" || $o == "¥r" || ord($o) == "13" || ord($o) == 0) {
                        continue;
                    }
                    //echo "addHistory('$aUrl[$k]', '$o');";
                    $k++;
                }
            }
            ?>


           //To Check and show previous results in **lastResults** div
           if (localStorage.getItem("history") != null)
           {
               var historyTmp = localStorage.getItem("history");
               var oldhistoryarrayDuplicates = historyTmp.split('|').reverse();

               var oldhistoryarray = [];
                $.each(oldhistoryarrayDuplicates, function(i, el){
                    if($.inArray(el, oldhistoryarray) === -1) oldhistoryarray.push(el);
                });


               $('#lastResults').empty();
               for(var i =0; i<oldhistoryarray.length; i++)
               {
                   $('#lastResults').before('<a href="./apa.php?url=' + encodeURIComponent(oldhistoryarray[i]) + '">'+oldhistoryarray[i]+'</p>');
               }
           }


} else {
    // Sorry! No Web Storage support..
}



</script>

