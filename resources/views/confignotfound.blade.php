@extends('layouts.app')
@section('title', 'Compare screenshots')

@section('headextra')
    <script defer src="https://unpkg.com/img-comparison-slider@7/dist/index.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/img-comparison-slider@7/dist/styles.css"/>
    <style>
    .coloured-slider {
        --divider-color: rgba(0, 0, 0, 0.5);
        --default-handle-opacity: 0;
    }
    </style>
@endsection

@section('content')
    This config does not exist, create a file in the config/sites folder with the name '{{ $config }}.json'.
    <pre>
{
    "domains": {
        "local": "https:\/\/localhost",
        "live": "https:\/\/puttheproductionsitehere.com"
    },
    "urls": []
}
    </pre>
@endsection