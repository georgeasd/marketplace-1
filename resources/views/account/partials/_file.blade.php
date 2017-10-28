<div class="col-sm-12 no-padding YOUR-FILE-BOX">
    <h4><a href="#">{{ $file->title }}</a></h4>
    <h5>{{ str_limit($file->overview_short, 150) }}</h5>
    <hr>
    <span>
        {{ $file->isFree() ? 'Free' : '$' . $file->price }}
    </span>
    @if(!$file->approved)
        <span>
            Pending approval
        </span>
    @endif

    <span>
       {{ $file->live ? 'Live' : 'Not Live' }}
    </span>
    <span>
        <a href="#">Make changes</a>
    </span>
</div>
