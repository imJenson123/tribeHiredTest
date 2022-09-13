<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    public function getPost(){
        $client = new Client();
        $res = $client->request('GET','https://jsonplaceholder.typicode.com/comments',[
            'headers' => [
                'Accept'     => '*/*',
            ]
        ]);
        $data = json_decode($res->getBody(),true);
        $comment_count = [];
        foreach($data as $val){
            if(isset($comment_count[$val['postId']])){
                $comment_count[$val['postId']]['counter'] = $comment_count[$val['postId']]['counter'] + 1;
            }else{
                $comment_count[$val['postId']]['postId'] = $val['postId'];
                $comment_count[$val['postId']]['counter'] = 1;
            }
        }
        $res = $client->request('GET','https://jsonplaceholder.typicode.com/posts',[
            'headers' => [
                'Accept'     => '*/*',
            ]
        ]);
        $data = json_decode($res->getBody(),true);
        $post = [];
        // foreach($data as $val){
        //     foreach($comment_count as $val2){
        //         if($val['id'] == $val2['postId']){
        //             $post[$val['id']] = $val;
        //             $post[$val['id']]['total_number_of_comments'] = $val2['counter'];
        //         };
        //     }
        // }

        //Solution to reduce looping
        foreach($comment_count as $val){
                $post[$val['postId']] = $data[array_search($val['postId'], array_column($data, 'id'))];
                $post[$val['postId']]['total_number_of_comments'] = $val['counter'];
        }
        $post = collect($post)->sortBy('total_number_of_comments')->reverse()->toArray();
        return $post;
    }

    public function getComment(Request $request){
        $client = new Client();
        $res = $client->request('GET','https://jsonplaceholder.typicode.com/comments',[
            'headers' => [
                'Accept'     => '*/*',
            ]
        ]);
        $data = json_decode($res->getBody(),true);
        if(isset($request->postId)){
            $data = array_filter($data, function($comment) use($request){
                return $comment['postId'] == $request->postId;
            });
        }
        if(isset($request->id)){
            $data = array_filter($data, function($comment) use($request){
                return $comment['id'] == $request->id;
            });
        }
        if(isset($request->name)){
            $data = array_filter($data, function($comment) use($request){
                // return $comment['name'] == $request->name;
                return ( strpos($comment['name'], $request->name) !== false );
            });
        }
        if(isset($request->email)){
            $data = array_filter($data, function($comment) use($request){
                return ( strpos($comment['email'], $request->email) !== false );
            });
        }
        if(isset($request->body)){
            // dd(array_keys(preg_grep('~' . $request->body . '~', array_column($data, 'body'))));
            $data = array_filter($data, function($comment) use ($request) {
                return ( strpos($comment['body'], $request->body) !== false );
            });
            // $data = array_filter($data, function($comment) use($request){
            //     return preg_grep('~' . $request->body . '~', array_column($comment, 'body'));
            // });
        }dd($data);
        return $data;
    }
}
