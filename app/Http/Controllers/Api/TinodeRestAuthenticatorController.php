<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TinodeRestAuthenticatorController extends Controller
{

    private function parseSecret($encoded_secret)
    {

        $secret = base64_decode($encoded_secret);

        return  explode(':', $secret);
    }
    function add()
    {
        return response()->json(['err' => 'unsupported']);
    }
    function auth(Request $request)
    {
        
        Log::info(json_encode($request->all()));
        if (!$request->isJson()) {
            return response()->json(['err' => 'malformed']);
        }

        $secret  = $this->parseSecret($request->get('secret'));
        $uname = $secret[0];
        $password = $secret[1];
        if(auth()->attempt(['phone' => $uname, 'password' => $password]))
        {
            
            $data = json_decode($this->dummy_data, true);
            $user = User::where('phone', $uname)->first();
            if($user->uid != null)
            {
                return response()->json(['rec' => [
                    'uid' => $user->uid,
                    'authlvl' => 'auth',
                    'features' => 'V',
                    "tags"=> ["email:aliii@example.com", "uname:aliii"]
                ]]);
            }
            else
            {
                $response = [
                    'rec' => [
                        "authlvl"=> "auth",
                        "tags"=> ["email:aliii@example.com", "uname:aliii"]
                    ],
                    'newacc' => [
                        'auth' => 'JRWPS',
                        'anon' => 'N',
                        'public' => [['fn'=> $user->first_name.' '.$user->last_name]],
                        //'public' => [],
                        'private' => []
                    ]
                ];
                Log::info(json_encode($response));
                return response()->json($response);
            }
            return response()->json(['err' => 'unsupported']);
            
        }
        return response()->json(['err' => 'not found']);
        
    }
    function link(Request $request)
    {
        Log::info(json_encode($request->all()));
        if (!$request->isJson()) {
            return response()->json(['err' => 'malformed']);
        }

        $secret  = $request->get('secret');
        $rec = $request->get('rec');
        if (!$rec || !$secret || empty($rec['uid'])) {
            return response()->json(['err' => 'malformed']);
        }
        $secret  = $this->parseSecret($request->get('secret'));
        $uname = $secret[0];
        $password = $secret[1];
        if(auth()->attempt(['phone' => $uname, 'password' => $password]))
        {
            $user = User::where('phone', $uname)->firstOrFail();
            if($user->uid !=null)
            {
                return response()->json(['err' => 'duplicate value']);
            }
            $user->uid = $rec['uid'];
            $user->save();
            return response()->json(['success' =>'200']);
        }
        else
        {
            try
            {
                $user = User::where('phone', $uname)->firstOrFail();
            }
            catch(Exception $ex)
            {
                return response()->json(['err' => 'not found']);
            }
           
        }
        

    }
    public function checkUnique()
    {
        return response()->json(['err' => 'unsupported']);
    }
    public function delete()
    {
        return response()->json(['err' => 'unsupported']);
    }
    public function generate()
    {
        return response()->json(['err' => 'unsupported']);
    }
    public function update()
    {
        return response()->json(['err' => 'unsupported']);
    }
    public function restrictedTagNamespaces()
    {
        return response()->json(['strarr' => []]);
    }
    public function __construct()
    {
        $this->dummy_data =
            '{
        "alice": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "auth",
          "features": "V",
          "password": "alice123",
          "private": "email:bob@example.com,email:carol@example.com,email:dave@example.com,email:eve@example.com,email:frank@example.com",
          "public": {
            "fn": "Alice Johnson",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGQAAAwEBAQAAAAAAAAAAAAAAAQUGAwQI/8QAKRAAAgEDAgQFBQAAAAAAAAAAAQIDAAQRBTEGIUFREhMUYYEiQnGCo//EABYBAQEBAAAAAAAAAAAAAAAAAAABAv/EAB4RAAICAgMBAQAAAAAAAAAAAAECABEDIRNB4YGR/9oADAMBAAIRAxEAPwD1TQ5YoZ5ikvFNzJBpywWsvl3d3ItvEw3Utuw91UM361CaFzSKXYKO475GjSLhS9kvdKX1bq95bs1vO22XQ4Jx0zgN+CKd53oDYuHQoxU9QNvjoaj9Tj1DU+MFGny28cOlw/U00RkBmk7AMMFUH9asCcVhDDFG80kcaI0z+Nyox4jgLk9zgAfAoy3qbxZeMlgN1X75cl9GF7pXFdxbahJbyDU4/URtDEY18yMBXGCx5lSh3+01Y965praKWeCWREaSFiY2ZQSpIwSD05Ej5rUNge+9FFakzZeQhq3W/nlT/9k=",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:alice@example.com"
          ],
          "uid": "QVuwC5jz9o4"
        },
        "bob": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "auth",
          "features": "V",
          "password": "bob1234",
          "private": "email:alice@example.com,email:carol@example.com,email:dave@example.com,email:eve@example.com,email:frank@example.com",
          "public": {
            "fn": "Bob Smith",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGgABAAEFAAAAAAAAAAAAAAAABQMAAQIECP/EACkQAAEDAgQFBAMAAAAAAAAAAAECAwQAEQUSITETIjJBgUJRUnFhofD/xAAXAQEBAQEAAAAAAAAAAAAAAAAAAgED/8QAHxEAAQUBAAIDAAAAAAAAAAAAEQABAgMSMXGhE2Hw/9oADAMBAAIRAxEAPwDqisb+4FWvv4obFC65i8RlD7rLamXVnIQLkKQBuPyaicsMQudk8RIPPacBvtVeKEw5x5M5yOp9choNhZUoDMg32JAA286U3SE9MUrntiFHbTXqIoTForMrHYKJLaFpDDx5hf1N07/CtSbAizVIMuOy8UdPEQFW+r1lsNsPCm2v5IZB536dFxG2oWLJjYblDRbUp1pJ5UG4ym3Ym6vvxT99NRUEWMxFa4cdpDSPigAD9VOe9zSuGGH5kprww9NxvC//2Q==",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:bob@example.com"
          ],
          "uid": "dGEN6zTltcI"
        },
        "carol": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "auth",
          "features": "V",
          "password": "carol123",
          "private": "email:alice@example.com,email:bob@example.com,email:dave@example.com,email:eve@example.com,email:frank@example.com",
          "public": {
            "fn": "Carol Xmas",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGQAAAwEBAQAAAAAAAAAAAAAAAQUGBAMI/8QAJRAAAQQCAQMEAwAAAAAAAAAAAQIDBAUAERITIVEGMUFxFCJh/8QAFQEBAQAAAAAAAAAAAAAAAAAAAQL/xAAdEQEAAgEFAQAAAAAAAAAAAAABABECAxIhMcFB/9oADAMBAAIRAxEAPwD1RrAO31hyPkuzL71JPr4816DXVnBEhUfiHXnlpC+HIg8UhBSTrSiVDuAP2cNPdfNBywWpYYcU1Ncuv6iVWE2WlWilMlSVdP6ISD3/AKT7feNsEBobjB8ZH0zyK71vfwJJ6blk43YRSo9nQGW2VpT5KS0CR4cTlhmC0qoFqwGbKHGlsg8g2+0HEg+dH5ysMjGx6SvfIJNgcSVFOxyA2RnTFlVS1tQlxNVXxIYcIK/x2Q3y17b0O+M8lr51Gf/Z",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:carol@example.com"
          ]
        },
        "dave": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "auth",
          "features": "V",
          "password": "dave123",
          "private": "email:alice@example.com,email:bob@example.com,email:carol@example.com,email:eve@example.com,email:frank@example.com",
          "public": {
            "fn": "Dave Goliathsson",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGQAAAwADAAAAAAAAAAAAAAAAAAQFAgMI/8QAIxAAAgIBAwQDAQAAAAAAAAAAAQIAAxEEITEFEhNBYXOxof/EABcBAQEBAQAAAAAAAAAAAAAAAAECAAP/xAAcEQACAwADAQAAAAAAAAAAAAABAgADERIxwUH/2gAMAwEAAhEDEQA/AOqMQ9wkxksu6lfX57URKkICEcktn18CcbH45g3YgSpCT6bbKtSlF9nkDglHIwduQcbShKRwwhCIVbdX1X01frx6LXaPTXv33UVWPjGXQE4kWKzYV+Hwj2Ii5ZdT1GrxN3JSGLMOMnYD9/kozBFWtQqAADgATZGusrpPZmM//9k=",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:dave@example.com"
          ]
        },
        "eve": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "auth",
          "features": "V",
          "password": "eve123",
          "private": "email:alice@example.com,email:bob@example.com,email:carol@example.com,email:dave@example.com,email:frank@example.com",
          "public": {
            "fn": "Eve Adams",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGQAAAwEBAQAAAAAAAAAAAAAAAQUGAgQI/8QAIxAAAQQCAAcBAQAAAAAAAAAAAgEDBAUAEQYSEyExYXFBkf/EABUBAQEAAAAAAAAAAAAAAAAAAAIB/8QAGxEAAwEBAQEBAAAAAAAAAAAAAQIRABIhA3H/2gAMAwEAAhEDEQA/APVKZlfiZrymSt6EiVxJAhtzpMZgor7pIwSIpEJtIm9ov4RYkXozJE6Ms1R81h/MV1dYcIjIp8yTzJrT5oqD80iY0yEAHzRgAfDcFyUv4EWfxhVtzY7MhsYMgkF0EJEXqM9++VZeMXWVRXWhtLZQIkvp7QFfZFzl351tO3hP5iRuTcvk/DXGtqYFdzrXw2I5Hrm6QIO9eN6zvL1rF1dS1taZnXwIcUyTREwyLaqnvSYyX3kJptuLmm2/u//Z",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:eve@example.com"
          ]
        },
        "frank": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "auth",
          "features": "V",
          "password": "frank123",
          "private": "email:alice@example.com,email:bob@example.com,email:carol@example.com,email:dave@example.com,email:eve@example.com",
          "public": {
            "fn": "Frank Singer",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAUCAwQI/8QAJxAAAQQBAgUEAwAAAAAAAAAAAQACAwQREjEFEyFBgQYUU2FxcpH/xAAXAQADAQAAAAAAAAAAAAAAAAAAAQID/8QAHBEBAAMBAQADAAAAAAAAAAAAAQACEQMSUaHh/9oADAMBAAIRAxEAPwDqfHQoIwcdkZ3SXibZZeMVK0diaKJ0ErzyyBkh0YHb7KYazPpfxXc39joeFJYaVR9bUXWZ5tXyuBx+MBbUMdVTUyBCQ8TrR2vUNBkzGvb7aY4P7RJ605VLomGZspa3WGlodjqAdxnwP4nVx2T15nSvl+T6ZCpSgqauRG1mrfHdat0I8qV2XWpUwJ//2Q==",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:frank@example.com"
          ]
        },
        "xena": {
          "anon": "N",
          "auth": "JRWPA",
          "authlvl": "root",
          "features": "V",
          "password": "xena123",
          "private": "",
          "public": {
            "fn": "Xena Peaceful Peasant",
            "photo": {
              "data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAgACADASIAAhEBAxEB/8QAGAABAQEBAQAAAAAAAAAAAAAABgAEBQj/xAArEAABAwMCBAQHAAAAAAAAAAABAgMEAAUREjEGISJhNEFRcRQjJTJCQ4L/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8A9U1VUeukuRar/EffcUu1TMRVg7R3s/LX7LzoPfR6mgQ1Ue4fmSbvOl3FLhTavDxG88ncHre9ieSeyc/lyQ0FRTi36y43ww2fGtlyaobtRQcHHopZ6R/RH20rrM1GYakOvtstIfeADiwkBS8bZPnjJoOBwbIcYadsM7R8baghoEJwHmP1OgbcwMEDZST5YpRWZUZgy0yiy2ZSUFsO6RqCSQSnO+MgHHatNB//2Q==",
              "type": "jpeg"
            }
          },
          "tags": [
            "email:xena@example.com"
          ]
        }
      }';
    }
    private $dummy_data;
}
