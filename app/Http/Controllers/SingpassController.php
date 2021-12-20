<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon\Carbon;
use SimpleJWT\Keys\KeySet;
use SimpleJWT\JWT;
use SimpleJWT\JWE;
use Illuminate\Support\Facades\Http;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA128KW;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWEDecrypterFactory;
use App\Models\Customer;

class SingpassController extends Controller
{
    public function login(Request $request)
    {   
        return view('singpass'); 
    }

    public function callback(Request $request) {
        $set = new KeySet();
        $set->load(file_get_contents(storage_path('app/jwks/') . 'private.json'));
        $headers = ['alg' => 'ES256', 'typ' => 'JWT'];
        $claims = [
            'sub' => env('SINGPASS_CLIENT_ID'),
            'aud' => env('SINGPASS_URL'), // till date, it's constant
            'iss' => env('SINGPASS_CLIENT_ID'),
            'iat' => Carbon::now()->timestamp, // use strtotime('now') or Carbon::now()->timestamp
            'exp' => Carbon::now()->addMinutes(2)->timestamp
        ];
        $jwt = new JWT($headers, $claims);
        try {
            $response = Http::asForm()->post(env('SINGPASS_URL').'/token', [
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $jwt->encode($set),
                'client_id' => env('SINGPASS_CLIENT_ID'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('SINGPASS_REDIRECT_URL'),
                'code' => $request->code
            ]);
            $response = json_decode($response->getBody(), true);

            // The input we want to decrypt
            $token = $response['id_token'];
            $jwk = new \Jose\Component\Core\JWK(
                json_decode(file_get_contents(storage_path('app/jwks/') . 'private.json'), true)['keys'][1]
            );
            // The key encryption algorithm manager with the A256KW algorithm.
            $keyEncryptionAlgorithmManager = new AlgorithmManager([
                new ECDHESA128KW(),
            ]);

            // The content encryption algorithm manager with the A256CBC-HS256 algorithm.
            $contentEncryptionAlgorithmManager = new AlgorithmManager([
                new A256CBCHS512(),
            ]);

            // The compression method manager with the DEF (Deflate) method.
            $compressionMethodManager = new CompressionMethodManager([
                new Deflate(),
            ]);

            // We instantiate our JWE Decrypter.
            $jweDecrypter = new JWEDecrypter(
                $keyEncryptionAlgorithmManager,
                $contentEncryptionAlgorithmManager,
                $compressionMethodManager
            );
            
            // The serializer manager. We only use the JWE Compact Serialization Mode.
            $serializerManager = new JWESerializerManager([
                new CompactSerializer(),
            ]);
        
            // We try to load the token.
            $jwe = $serializerManager->unserialize($token);
            // We decrypt the token. This method does NOT check the header.
            $success = $jweDecrypter->decryptUsingKey($jwe, $jwk, 0);
            
            $result = JWT::deserialise($jwe->getPayload());
            //return $result['claims']['sub'];
            $nric = explode("=", explode(",", $result['claims']['sub'])[0])[1]; 
            $customer = Customer::where('nric_uen', $nric)->first(); 
            if($customer != null){
                return redirect('/singpass/success?token=' . $customer->createToken('Personal Access Token')->plainTextToken);
            } else {
                return redirect('/singpass/success?nric=' . $nric);
            }
            // Pass back sanctum token if NRIC match
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(['message' => 'An error has occured.'], 422);    
        }
    }

    public function jwks(){
        $file = storage_path('app/jwks/') . 'jwks.json';
        if (file_exists($file)) {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            return response()->file($file, $headers);
        } else {
            abort(404, 'File not found!');
        }
    }

    public function success(){
        return view('success');
    }
}