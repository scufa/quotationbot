<?php

function tut_m_command_result($upd_data)
{
    if (!isset($upd_data['command'])) { //no command in data
        $result['text'] = apply_filters('gwptb_output_text', 'К сожалению, вы отправили неверный запрос.');
        return $result;
    }

    $result = [];
    $args = [];
    $per_page = 5;

    $args = [
        'post_type' => 'place',
        'posts_per_page' => $per_page,
        'paged' => 1
    ];

    if (false !== strpos($upd_data['content'], 'paged=')) {
        //more search
        parse_str($upd_data['content'], $a);

        if (isset($a['paged'])) {
            $args['paged'] = (int) $a['paged'];
        }
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $result['parse_mode'] = 'HTML';
        $keys = ['inline_keyboard' => []];

        //list
        $paged = $args['paged'];
        if ($query->found_posts > $per_page) {
            $end = ($paged * $per_page < $query->found_posts) ? $paged * $per_page : $query->found_posts;
            $result['text'] = sprintf('Найдено %d / показано %d - %d', $query->found_posts, ($paged * $per_page - $per_page) + 1, $end).chr(10).chr(10);
        } else {
            $result['text'] = sprintf('Найдено - %d', $query->found_posts).chr(10).chr(10);
        }

        $result['text'] .= tut_format_places_list($query->posts);
        $result['text'] = apply_filters('gwptb_output_html', $result['text']);

        //nex/prev keys
        if ($paged > 1) {
            $keys['inline_keyboard'][0][] = ['text' => 'Пред.', 'callback_data' => 'm='.$s.'&paged='.($paged - 1)];
        }

        if ($paged < ceil($query->found_posts / $per_page)) {
            $keys['inline_keyboard'][0][] = ['text' => 'След.', 'callback_data' => 'm='.$s.'&paged='.($paged + 1)];
        }

        //donation button
        $donation_url = \Tutbot\Core::getDonationUrl();
        if (!empty($donation_url)) {
            $keys['inline_keyboard'][][] = ['text' => 'Сделать пожертвование', 'url' => $donation_url];
        }
        $result['reply_markup'] = json_encode($keys);
    } else {
        $result['text'] = 'К сожалению, по вашему запросу ничего не найдено.';
        $result['text'] = apply_filters('gwptb_output_text', $result['text']);
    }

    return $result;
}

function tut_q_command_result($upd_data)
{
    //add command param to $upd_data
    //$result['text'] = 'command '.$upd_data['command']; return$result;

    if (!isset($upd_data['command']) || $upd_data['command'] != 'q') { //no command in data
        $result['text'] = apply_filters('gwptb_output_text', 'К сожалению, вы отправили неверный запрос.');
        return $result;
    }


    $result = [];
    $args = [];
    $per_page = 2;
    $s = '';

    if (false !== strpos($upd_data['content'], 'next=')) { //update
        //more random
        $args = [
            'post_type' => 'quote',
            'posts_per_page' => 1,
            'orderby' => 'rand'
        ];
    } elseif (false !== strpos($upd_data['content'], 'paged=')) {
        //more search
        parse_str($upd_data['content'], $a);

        if (isset($a['q']) && isset($a['paged'])) {
            $args = [
                'post_type' => 'quote',
                'posts_per_page' => $per_page,
                's' => apply_filters('gwptb_search_term', $a['q']),
                'paged' => (int) $a['paged']
            ];
        }
    } else {
        //have search term
        $self = Gwptb_Self::get_instance();
        $s = apply_filters('gwptb_search_term', str_replace(['@', '/q', $self->get_self_username()], '', $upd_data['content']));


        if (!empty($s)) { //initial search
            $args = [
                'post_type' => 'quote',
                'posts_per_page' => $per_page,
                's' => $s,
                'paged' => 1
            ];
        } else { //random quote
            $args = [
                'post_type' => 'quote',
                'posts_per_page' => 1,
                'orderby' => 'rand'
            ];
        }
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $result['parse_mode'] = 'HTML';
        $keys = ['inline_keyboard' => []];

        if (isset($args['s'])) { //search results buttons
            //list
            $paged = $args['paged'];
            if ($query->found_posts > $per_page) {
                $end = ($paged * $per_page < $query->found_posts) ? $paged * $per_page : $query->found_posts;
                $result['text'] = sprintf('Найдено %d / показано %d - %d', $query->found_posts, ($paged * $per_page - $per_page) + 1, $end).chr(10).chr(10);
            } else {
                $result['text'] = sprintf('Найдено - %d', $query->found_posts).chr(10).chr(10);
            }

            $result['text'] .= tut_format_quotes_list($query->posts);
            $result['text'] = apply_filters('gwptb_output_html', $result['text']);

            //nex/prev keys
            if ($paged > 1) {
                $keys['inline_keyboard'][0][] = ['text' => 'Пред.', 'callback_data' => 'q='.$s.'&paged='.($paged - 1)];
            }

            if ($paged < ceil($query->found_posts / $per_page)) {
                $keys['inline_keyboard'][0][] = ['text' => 'След.', 'callback_data' => 'q='.$s.'&paged='.($paged + 1)];
            }
        } else { //random quote button
            $p = reset($query->posts);

            $result['text'] = ''; //sprintf('# %d. ', $p->ID);
            $result['text'] .= $p->post_content;
            $result['text'] = apply_filters('gwptb_output_text', $result['text']);

            $keys['inline_keyboard'][0][] = ['text' => 'Еще цитата', 'callback_data' => 'q=1&next=1'];
        }

        //donation button
        $donation_url = \Tutbot\Core::getDonationUrl();
        if (!empty($donation_url)) {
            $keys['inline_keyboard'][][] = ['text' => 'Сделать пожертвование', 'url' => $donation_url];
        }


        //add buttons

        $result['reply_markup'] = json_encode($keys);
    } else {
        $result['text'] = 'К сожалению, по вашему запросу ничего не найдено.';
        $result['text'] = apply_filters('gwptb_output_text', $result['text']);
    }

    return $result;
}

function tut_format_quotes_list($posts)
{
    $out = '';

    foreach ($posts as $p) {
        $out .= $p->post_content.chr(10).chr(10);
    }

    return $out;
}

function tut_format_places_list($posts)
{
    $out = '';

    foreach ($posts as $p) {
        $out .= "<b>".$p->post_title."</b>\n";
        $out .= $p->post_content."\n";
        $out .= tut_get_map_link($p)."\n";
        $out .= "----------------------------------\n";
    }
    return $out;
}

function tut_get_map_link($p)
{

    $map = 'https://yandex.ru/maps/?text='.urlencode($p->post_content);
    $map_link = "<a href='{$map}'>[карта]</a>";

    return $map_link;
}
