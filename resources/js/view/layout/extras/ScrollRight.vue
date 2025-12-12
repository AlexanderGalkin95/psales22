<template>
  <div id="scroll-right" ref="scrollright" class="scroll-right" @click="$emit('click', $event)">
    <span class="svg-icon">
      <inline-svg src="/media/svg/icons/Navigation/Right-2.svg" />
    </span>
  </div>
</template>

<script>

export default {
  name: "ScrollRight",
  mounted() {
  }
};
</script>

<style lang="scss">
.scroll-right {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 36px;
  height: 36px;
  position: fixed;
  margin-top: 8px;
  right: 20px;
  cursor: pointer;
  z-index: 100;
  background-color: #F64E60;
  box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
  opacity: 0.3;
  transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
  border-radius: 0.85rem !important;

  .svg-icon {
    svg {
      height: 24px;
      width: 24px;
    }
  }
  > i {
    font-size: 1.3rem;
    color: #ffffff;
  }
  &:hover {
    opacity: 1;
    > i {
      color: #ffffff;
    }
    .svg-icon {
      svg {
        g [fill] {
          transition: fill 0.3s ease;
          fill: #ffffff !important;
        }
      }
    }
  }
}
</style>