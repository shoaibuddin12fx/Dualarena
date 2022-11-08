<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Validator;


class ChatRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $chatrooms = ChatRoom::all();

        return self::success('Chat Rooms', [ 'data' => $chatrooms ] );
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:chat_rooms',            
        ]);

        if ($validator->fails()) {
            return self::failure($validator->errors()->first(), ['data' => []]);
        }

        $data = $request->all();
        
        $chatRoom = new ChatRoom();
        $chatRoom->name = $data['name'];
        $chatRoom->description = isset($data['description']) ? $data['description'] : "" ;
        $chatRoom->save();

        return self::success('User created', ['data' => $chatRoom]);


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function addUserInChatRoom(Request $request){

        $validator = Validator::make($request->all(), [
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return self::failure($validator->errors()->first(), ['data' => []]);
        }

        $data = $request->all();

        $chatRoom = new ChatRoom();
        $chatRoom->name = $data['name'];
        $chatRoom->description = isset($data['description']) ? $data['description'] : "";
        $chatRoom->save();

        return self::success('User created', ['data' => $chatRoom]);


    }
}