# UoPeople
Convenient Tools for Students of University of the People

# What's this?
I thought it's convenient if I can shrink all posts in the Discussion Forums of University of the People Moodle.

![](https://kujiy.github.io/uopeople/docs/db.png)

So I made a bookmarklet to shrink all posts in the discussion forum.


# How to use

1. [Open this page](https://kujiy.github.io/uopeople/docs/)

2. Drag the **Shrink!** link into the bookmark bar of your browser.



3. Open discussion forum



4. Click the bookmarklet you dragged to your bookmark bar 



5. If all of the posts are shrunk in short boxes, it means working correctly.



---


### Just for developer

```js
function expand_this_post(o) {
	$(o).parent().parent().find(".row.maincontent, .row.side").each(
		function(){ 
							$(this).show(); 
						} 
	);
	$(o).parent().parent().find(".post_abbr").each(
		function() {
			$(this).hide();
		}
	);
}
// Shrink the post
$(".row.maincontent, .row.side").each(function(){$(this).hide()});
// Add the expand link
$(".forumpost").each(function(){ 
	post = '<div class="post_abbr">' + $(this).find(".row.maincontent").text().slice(0, 70) + '...';
	post += '<a onclick="expand_this_post(this);">Expand</a></div>' ; 
	 out =  $(this).html() + post ;
	 $(this).html(out);
 });
// Colorize the second posts
$(".firstpost ~ div > div.forumpost").each(function(){$(this).css('box-shadow', '-20px 0 0 #76AFB5')});
// Numbering posts
// TODO: make recursively
$(".firstpost ~ div ").each(function(i){
    $(this).find(".forumpost").each(function(j) {
              $(this).prepend( "<span class='breadcrumb' style='float: left;margin-left: -35px;'>" + (i + 1) + '-' + (j + 1) + '. </span>');
    });
});
```






