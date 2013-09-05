    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        {if empty($maincontent)}
            {blockposition name=center}
        {else}
            {$maincontent}
        {/if}
      </div>
    </div>

    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-lg-4">
          {blockposition name=center}
        </div>
        <div class="col-lg-4">
          {blockposition name=center}
       </div>
        <div class="col-lg-4">
          {blockposition name=center}
          </div>
      </div>