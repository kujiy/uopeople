// open all dbs
FIND="Discussion Forum";
i=0;
$("a").each(function(){   
    str = $(this).text();
    if (str.match(FIND)) {
        console.log(str);    
     link = $(this).attr("href");
     console.log(link);
     $.ajax({
           url: link,
     }).done(function(data) {
              val = $("td a", data.responseXML).text(); //can't parse with jquery selector
             console.log(val);
     });
    }
    i = i + 1;
 });
 
// show all db links (works)
FIND="Discussion Forum";
RES="";
$("a").each(function(){   
    str = $(this).text();
    if (str.match(FIND)) {
        console.log(str);    
        link = $(this).attr("href");
         RES=RES+"<a href='"+link+"'>"+link+"</a><br><br>";
    }
 });
 document.write(RES);


