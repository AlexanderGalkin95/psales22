<template>
  <div class="ar-player">
    <div class="ar-player-actions">
      <icon-button
        id="play"
        class="ar-icon ar-icon__lg ar-player__play"
        :name="playBtnIcon"
        :class="{'ar-player__play--active': isPlaying}"
        @click.native="playback"/>
    </div>

    <div class="ar-player-bar">
      <line-control
        class="ar-player__progress"
        ref-id="progress"
        :percentage="progress"
        @change-linehead="_onUpdateProgress"/>
      <div class="ar-player__time text-danger">
        <em style="padding: 0 2px;">{{playedTime}}</em>/
        <span style="padding: 0 2px;">{{duration}}</span>
      </div>
      <volume-control @change-volume="_onChangeVolume"/>
      <div class="dropdown">
        <div style="max-width: 68px;margin-left: 5px; cursor: pointer;">
          <div class="control--select--button">
          {{ activeRate }}
        </div>
        </div>
        <div class="dropdown-content">
          <a v-for="(pr, index) in playbackRates"
             :key="index"
             href="javascript:void(0);"
             :class="{ 'active': pr.selected }"
             @click="setPlaybackRate(pr)">
            <i v-show="pr.selected" class="fa fa-1x fa-check text-danger"></i> {{ pr.label }}
          </a>
        </div>
      </div>
    </div>

    <audio :ref="playUniqueRef" :id="playerUniqId" :src="audioSource"></audio>
  </div>
</template>

<script>
  import IconButton    from './icon-button'
  import LineControl   from './line-control'
  import VolumeControl from './volume-control'
  import { convertTimeMMSS } from '../lib/utils'
  import _ from 'lodash'

  export default {
    name: 'Player',
    props: {
      id: { type: String },
      src: { type: String },
      playRef: { type: String },
      filename : { type: String },
      autoplay : { type: Boolean, default: false }
    },
    data () {
      return {
        isPlaying  : false,
        duration   : convertTimeMMSS(0),
        playedTime : convertTimeMMSS(0),
        progress   : 0,
        player   : null,
        playbackRates: [
          { label: '1х (обычная)', value: 1, selected: false },
          { label: '1.25х', value: 1.25, selected: false },
          { label: '1.5х', value: 1.5, selected: false },
          { label: '2х', value: 2, selected: false },
        ],
        activeRate: '1x',
      }
    },
    components: {
      IconButton,
      LineControl,
      VolumeControl
    },
    mounted () {
      this.player = document.getElementById(this.playerUniqId)

      this.player.addEventListener('ended', (event) => {
        this.isPlaying = false
        this.$emit('audio-ended', event)
      })
      this.player.addEventListener('pause', (event) => {
        this.$emit('audio-paused', event)
      })
      this.player.addEventListener('play', (event) => {
        this.isPlaying = true
        this.$emit('audio-play', event)
      })

      this.player.addEventListener('loadeddata', (event) => {
        this.$emit('audio-loaded-data', event)
        this._resetProgress()
        this.duration = convertTimeMMSS(this.player.duration)
        if (this.autoplay) {
          setTimeout(() => {
            this.playback()
          }, 100)
        }
      })

      this.player.addEventListener('timeupdate', this._onTimeUpdate)

      this.player.addEventListener('ratechange', (event) => {
        this.$emit('audio-rate-change', event)
      });

      this.$eventBus.$on('remove-record', () => {
        this._resetProgress()
      })
    },
    computed: {
      audioSource () {
        return this.src ? this.src : this._resetProgress()
      },
      playBtnIcon () {
        return this.isPlaying ? 'pause' : 'play'
      },
      playerUniqId () {
        return this.id !== undefined ? this.id : `audio-player${this._uid}`
      },
      playUniqueRef () {
        return this.playRef !== undefined ? this.playRef : `audioPlayer`
      },
    },
    methods: {
      setPlaybackRate (rate) {
        this.player.playbackRate = rate.value
        _.forEach(this.playbackRates, item => {
          item.selected = false
        })
        rate.selected = true
        this.activeRate = rate.label
        this.$emit('audio-playback-rate-change', rate.value)
      },
      playback () {
        if (!this.audioSource) {
          return
        }

        if (this.isPlaying) {
          this.player.pause()
        } else {
          setTimeout(() => {
            this.player.play()
          }, 0)
        }

        this.isPlaying = !this.isPlaying
      },
      stopPlay() {
        this.player.stop()
        this.isPlaying = false
        this.$emit('audio-stopped', null)
      },
      _resetProgress () {
        if (this.isPlaying) {
          this.player.pause()
        }

        this.duration   = convertTimeMMSS(0)
        this.playedTime = convertTimeMMSS(0)
        this.progress   = 0
        this.isPlaying  = false
        this.$emit('audio-reset-progress', this.progress)
      },
      _onTimeUpdate () {
        this.playedTime = convertTimeMMSS(this.player.currentTime)
        this.progress = (this.player.currentTime / this.player.duration) * 100
        this.$emit('audio-time-update', this.progress)
      },
      _onUpdateProgress (pos) {
        if (pos) {
          this.player.currentTime = pos * this.player.duration
        }
      },
      _onChangeVolume (val) {
        if (val) {
          this.player.volume = val
          this.$emit('audio-volume-change', val)
        }
      }
    },
    beforeDestroy() {
      this.$emit('audio-ended', null)
    }
  }
</script>

<style lang="scss">
@import '../../../assets/sass/components/variables.demo';
@import '../scss/icons';
.ar-player__play {
  box-shadow: none!important;
}

.ar-player {
  height: unset;
  border: 0;
  border-radius: 0;
  display: flex;
  flex-flow: wrap;
  flex-direction: row;
  align-items: center;
  background-color: unset;

  & > .ar-player-bar {
    border: 1px solid #E8E8E8;
    border-radius: 24px;
    margin: 0 0 0 5px;

    & > .ar-player__progress {
      position: relative;
      overflow: hidden;
      align-self: center;
      flex: 1 0 auto;
      height: 100%;
      display: flex;
      align-items: center;
      margin: 0 8px;
      &:before {
        content: "";
        position: absolute;
        top: calc(50% - 2.5px);
        width: 100%;
        height: 5px;
        border-radius: 2px;
        background: #ddd;
        border: 0;
        display: flex;
      }
    }
  }

  &-bar {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    height: 38px;
    padding: 0 12px;
    margin: 0 5px;
  }

  &-actions {
    flex: 0 1 auto;
    align-items: center;
    justify-content: space-around;
  }

  &__time {
    color: rgba(84,84,84,0.5);
    flex-shrink: 0;
    padding: 0 12px;
  }

  &__play {
    width: 38px;
    height: 38px;
    background-color: #FFFFFF;
    box-shadow: 0 2px 11px 11px rgba(0,0,0,0.07);

    &--active {
      fill: white !important;
      background-color: $primary !important;
    }
  }
}
.dropdown {
  position: relative;
  display: inline-block;
}
.dropdown-content {
  display: none;
  position: absolute;
  background-color: #fcfcfc;
  min-width: 50px;
  box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
  z-index: 1;
  left: -15px;
}
.dropdown:hover .dropdown-content {display: block;}
.dropdown-content a {
  color: black;
  text-align: center;
  padding: 3px;
  text-decoration: none;
  font-size: smaller;
  display: block;
  &:hover {
    background-color: #efe7e7;
  }
  &.active {
    background-color: #efe7e7;
  }
}

@media (max-width: 575.98px) {
  .ar-player {
    width: 100% !important;
  }
}

.control--select--button {
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  box-sizing: border-box;
  max-width: 100%;
  display: inline-block;
  width: 100%;
  height: 100%;
  text-align: left;
  background: linear-gradient(to bottom,#fcfcfc 0%,#f8f8f9 100%);
  padding-right: 25px;
  padding-left: 9px;
  color: inherit;
  &:after {
    content: "";
    position: absolute;
    top: calc(50% - 5px);
    width: 6px;
    height: 6px;
    border-bottom: 1px solid #000;
    border-right: 1px solid #000;
    transform: rotate(45deg);
    margin-left: 7px;
    right: 5px;
    z-index: 10;
  }
}

</style>