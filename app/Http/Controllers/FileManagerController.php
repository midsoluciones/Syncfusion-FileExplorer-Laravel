<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Storage;

/**
 * Class FileManagerController
 * @package App\Http\Controllers
 */
class FileManagerController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function actions(Request $request)
    {
        $action = $request->input('ActionType');
        $responseData = null;
        switch ($action) {
            case 'Read':
                $path = $request->input('Path');
                $extensionsAllow = $request->input('ExtensionsAllow');
                $selectedItems = $request->input('SelectedItems');
                $responseData = [
                    'files' => $this->read($path, $extensionsAllow, $selectedItems),
                    'cwd' => [
                        'dateModified' => date('Y/m/d h:i:s', Storage::lastModified($path)),
                        'isFile' => false,
                        'name' => basename($path),
                        'hasChild' => false,
                        'size' => Storage::size($path),
                        'type' => Storage::mimeType($path)
                    ]
                ];
                break;
            case 'CreateFolder':
                $path = $request->input('Path');
                $name = $request->input('Name');
                $selectedItems = $request->input('SelectedItems');
                $responseData = [
                    'files' => $this->createFolder($path, $name, $selectedItems),
                    'details' => null,
                    'error' => null
                ];
                break;
            case 'Remove':
                $path = $request->input('Path');
                $names = $request->input('Names');
                $selectedItems = $request->input('SelectedItems');
                $this->remove($path, $names, $selectedItems);
                $responseData = [
                    'error' => null
                ];
                break;
            case 'Upload':
                $path = $request->input('Path');
                $fileUpload = $request->file('FileUpload');
                $selectedItems = $request->input('SelectedItems');
                $this->upload($path, $fileUpload, $selectedItems);
                $responseData = [
                    'error' => null
                ];
                break;
            case 'Rename':
                $path = $request->input('Path');
                $name = $request->input('Name');
                $newName = $request->input('NewName');
                $commonFiles = $request->input('CommonFiles');
                $selectedItems = $request->input('SelectedItems');
                $this->rename($path, $name, $newName, $commonFiles, $selectedItems);
                $responseData = [
                    'error' => null
                ];
                break;
            case 'GetDetails':
                $path = $request->input('Path');
                $names = $request->input('Names');
                $selectedItems = $request->input('SelectedItems');
                $responseData = [
                    'details' => $this->getDetails($path, $names, $selectedItems)
                ];
                break;
            case 'Download':
                $path = $request->input('Path');
                $names = $request->input('Names');
                $selectedItems = $request->input('SelectedItems');
                return response()->download($this->download($path, $names, $selectedItems));
                break;
            case 'GetImage':
                $path = $request->input('Path');
                $selectedItems = $request->input('SelectedItems');
                return response()->download($this->download($path, null, $selectedItems, true));
                break;
            default:
                break;
        }

        return response()->json($responseData);
    }

    /**
     * @param $path
     * @param null $extensionsAllow
     * @param array $selectedItems
     * @return array
     */
    private function read($path, $extensionsAllow = null, $selectedItems = [])
    {
        $files = Storage::files($path);
        $directories = Storage::directories($path);
        $items = array_merge($files, $directories);

        $allFiles = [];

        foreach ($items as $item) {
            $mimeType = Storage::mimeType($item);
            array_push($allFiles, [
                'name' => basename($item),
                'hasChild' => $mimeType == 'directory',
                'isFile' => $mimeType != 'directory',
                'type' => $mimeType,
                'dateModified' => date('Y/m/d h:i:s', Storage::lastModified($item)),
                'size' => Storage::size($item)
            ]);
        }

        return $allFiles;
    }

    /**
     * @param $path
     * @param string $name
     * @param array $selectedItems
     * @return array
     */
    private function createFolder($path, $name = 'New Folder', $selectedItems = [])
    {
        $file = "$path/$name";
        Storage::makeDirectory($file);

        $allFiles = [];

        $mimeType = Storage::mimeType($file);
        $fileObject = [
            'name' => basename($file),
            'hasChild' => $mimeType == 'directory',
            'isFile' => $mimeType != 'directory',
            'type' => $mimeType,
            'dateModified' => date('Y/m/d h:i:s', Storage::lastModified($file)),
            'size' => Storage::size($file)
        ];
        array_push($allFiles, $fileObject);

        return $allFiles;
    }

    /**
     * @param $path
     * @param array $names
     * @param array $selectedItems
     */
    private function remove($path, $names = [], $selectedItems = [])
    {
        foreach ($names as $name) {
            $file = "$path/$name";
            $type = Storage::mimeType($file);
            $type == 'directory' ? Storage::deleteDirectory($file) : Storage::delete($file);
        }
    }

    /**
     * @param $path
     * @param $fileUpload
     * @param array $selectedItems
     */
    private function upload($path, $fileUpload, $selectedItems = [])
    {
        Storage::putFileAs($path, $fileUpload, $fileUpload->getClientOriginalName());
    }

    /**
     * @param $path
     * @param $name
     * @param $newName
     * @param array $commonFiles
     * @param array $selectedItems
     */
    private function rename($path, $name, $newName, $commonFiles = [], $selectedItems = [])
    {
        $fileOld = "$path/$name";
        $fileNew = "$path/$newName";
        Storage::move($fileOld, $fileNew);
    }

    /**
     * @param $path
     * @param array $names
     * @param array $selectedItems
     * @return array
     */
    private function getDetails($path, $names = [], $selectedItems = [])
    {
        $files = [];
        foreach ($names as $name) {
            $file = "$path/$name";
            $fileDetails = [
                'CreationTime' => 'Unknown',
                'Extension' => File::extension($file),
                'FullName' => $file,
                'Format' => Storage::mimeType($file),
                'LastWriteTime' => date('Y/m/d h:i:s', Storage::lastModified($file)),
                'LastAccessTime' => 'Unknown',
                'Length' => Storage::size($file),
                'Name' => File::name($file)
            ];
            array_push($files, $fileDetails);
        }
        return $files;
    }

    /**
     * @param $path
     * @param $name
     * @param $selectedItems
     * @param bool $isImage
     * @return mixed
     */
    private function download($path, $name, $selectedItems, $isImage = false)
    {
        $file = $isImage ? $path : "$path/$name";
        $filePath = Storage::getDriver()->getAdapter()->applyPathPrefix($file);
        return $filePath;
    }
}
