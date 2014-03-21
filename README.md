Wordpress DNDCounter
====================

Inspired to <strong><a href="http://wordpress.org/plugins/author-page-views/" target="_blank" title="open new page">Author Page Views</a></strong>, this Wordpress plugin keeps track of every single view of every single post of the blog. The home page and the single pages will be ignored.

On every post are incremented the user views count for that post and stored the user IP address with a custom expiration datetime (default: 1 day).
On the Dashboard there is a new option that allows the admin to view a complete report per page (Post ID, Post Title, Views), filter per date range and export as a .CSV file.

Is also available an internal function (added as Wordpress function) to get the most popular (the most viewed) posts.
The number of popular posts is customizable and you are free to format the returned array as you prefer in your template.

The function is the following

<code>
DNDCounter::get_popular_posts(number_of_posts);
</code>

where 'number_of_posts' if 3 by default.
It returns an associative array with the following structure

<code>
$popular_posts[post_id]['image'] // post thumbnail image
</code>

<code>
$popular_posts[post_id]['title'] // post title
</code>

<code>
$popular_posts[post_id]['link'] // post link
</code>

<code>
$popular_posts[post_id]['count'] // views count
</code>
