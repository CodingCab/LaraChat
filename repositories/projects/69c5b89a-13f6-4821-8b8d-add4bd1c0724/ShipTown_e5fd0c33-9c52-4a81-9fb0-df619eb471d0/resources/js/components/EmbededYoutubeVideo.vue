<template>
  <div class="embed-responsive embed-responsive-16by9">
    <iframe
        class="embed-responsive-item"
        :src="video_url"
        :title="title"
        frameborder="0"
        :allow="allow"
        allowfullscreen
    ></iframe>
  </div>
</template>
<script>
export default {
  name: 'embeded-youtube-video',
  props: {
      video_url: "",
      title: "",
      allow: "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
  }
}
</script>
