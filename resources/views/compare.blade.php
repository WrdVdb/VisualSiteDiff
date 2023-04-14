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
    /*
    .coloured-slider::after{
        content:" ";
        position:absolute;
        top:0;
        left:0;
        right:0;
        bottom:0;
        background-image: url({{ $diff }});
        opacity:0.5;
    }
    */
    </style>
@endsection

@section('content')
    <div><a href="{{ route('diff.index', ['config' => $config]) }}">Back to diff index</a></div>
    
        <img-comparison-slider class="coloured-slider rendered" tabindex="0">
            <img slot="first" src="{{ $img1 }}" />
            <img slot="second" src="{{ $img2 }}" />
        </img-comparison-slider>
        
@endsection