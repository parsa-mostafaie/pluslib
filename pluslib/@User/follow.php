<?php
//NOTE THIS FILE IS CUSTOMIZED

function follows($follower, $followed)
{
    return select_q('follow', 'count(*)', condition: 'user_follower = ? AND user_followed = ?', p: [$follower, $followed])->fetchColumn();
}

function followers($followed)
{
    return select_q('follow', 'user_follower as follower', condition: 'user_followed = ?', p: [$followed])->fetchAll(PDO::FETCH_ASSOC);
}
function followings($follower)
{
    return select_q('follow', 'user_followed as following', condition: 'user_follower = ?', p: [$follower])->fetchAll(PDO::FETCH_ASSOC);
}

function follow($follower, $followed)
{
    return insert_q('follow', 'user_followed, user_follower', '?, ?', params: [$followed, $follower]);
}

function unfollow($follower, $followed)
{
    return delete_q('follow', 'user_followed=? AND user_follower=?', [$followed, $follower]);
}