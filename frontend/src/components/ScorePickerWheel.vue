<template>
  <Teleport to="body">
    <Transition name="ga-picker-fade">
      <div
        v-if="modelValue !== null"
        class="ga-score-picker-overlay"
        @click.self="close"
      >
        <div class="ga-score-picker-sheet" @click.stop>
          <div class="ga-score-picker-header">
            <button type="button" class="ga-score-picker-cancel" @click="close">
              キャンセル
            </button>
            <span class="ga-score-picker-title">点数を選択</span>
            <button type="button" class="ga-score-picker-done" @click="confirm">
              完了
            </button>
          </div>
          <div class="ga-score-picker-wheel-wrap">
            <div class="ga-score-picker-fade ga-score-picker-fade--top" aria-hidden="true" />
            <div class="ga-score-picker-fade ga-score-picker-fade--bottom" aria-hidden="true" />
            <div class="ga-score-picker-highlight" aria-hidden="true" />
            <div
              ref="scrollRef"
              class="ga-score-picker-scroll"
              @scroll="onScroll"
              @wheel.prevent="onWheel"
            >
              <div class="ga-score-picker-spacer" />
              <button
                v-for="n in max + 1"
                :key="n - 1"
                type="button"
                class="ga-score-picker-item"
                :class="{ 'ga-score-picker-item--selected': (n - 1) === selected }"
                @click="selectValue(n - 1)"
              >
                {{ n - 1 }}
              </button>
              <div class="ga-score-picker-spacer" />
            </div>
          </div>
          <div class="ga-score-picker-footer">
            <span class="ga-score-picker-max">0 ～ {{ max }}点</span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue: number | null
    max?: number
  }>(),
  { max: 100 }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: number | null): void
  (e: 'confirm', value: number): void
}>()

const scrollRef = ref<HTMLElement | null>(null)
const selected = ref(0)
const itemHeight = 44

watch(
  () => props.modelValue,
  (val) => {
    if (val !== null) {
      const v = Math.max(0, Math.min(props.max, val))
      selected.value = v
      nextTick(() => scrollToIndex(v))
    }
  },
  { immediate: true }
)

function scrollToIndex(index: number) {
  const el = scrollRef.value
  if (!el) return
  const top = index * itemHeight
  el.scrollTo({ top, behavior: 'auto' })
}

function getIndexFromScroll(): number {
  const el = scrollRef.value
  if (!el) return 0
  const scrollTop = el.scrollTop
  const index = Math.round(scrollTop / itemHeight)
  return Math.max(0, Math.min(props.max, index))
}

function onScroll() {
  const idx = getIndexFromScroll()
  if (idx !== selected.value) selected.value = idx
}

function onWheel(e: WheelEvent) {
  const el = scrollRef.value
  if (!el) return
  const delta = e.deltaY > 0 ? 1 : -1
  const next = Math.max(0, Math.min(props.max, selected.value + delta))
  selected.value = next
  scrollToIndex(next)
}

function selectValue(value: number) {
  selected.value = value
  scrollToIndex(value)
}

function close() {
  emit('update:modelValue', null)
}

function confirm() {
  emit('update:modelValue', null)
  emit('confirm', selected.value)
}
</script>

<style scoped>
.ga-score-picker-overlay {
  position: fixed;
  inset: 0;
  z-index: 2400;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  -webkit-tap-highlight-color: transparent;
}

.ga-score-picker-sheet {
  background: rgba(242, 242, 247, 0.98);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 20px 20px 0 0;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.15);
  padding-bottom: env(safe-area-inset-bottom, 0);
  max-height: 60vh;
}

.ga-score-picker-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 0.5px solid rgba(60, 60, 67, 0.12);
}

.ga-score-picker-cancel {
  font-size: 17px;
  font-weight: 400;
  color: var(--ga-brand);
  background: none;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.ga-score-picker-title {
  font-size: 17px;
  font-weight: 600;
  color: var(--ga-text);
}

.ga-score-picker-done {
  font-size: 17px;
  font-weight: 600;
  color: var(--ga-brand);
  background: none;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.ga-score-picker-wheel-wrap {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.ga-score-picker-fade {
  position: absolute;
  left: 0;
  right: 0;
  height: 88px;
  pointer-events: none;
}

.ga-score-picker-fade--top {
  top: 0;
  background: linear-gradient(to bottom, rgba(242, 242, 247, 0.98) 0%, rgba(242, 242, 247, 0) 100%);
}

.ga-score-picker-fade--bottom {
  bottom: 0;
  background: linear-gradient(to top, rgba(242, 242, 247, 0.98) 0%, rgba(242, 242, 247, 0) 100%);
}

.ga-score-picker-highlight {
  position: absolute;
  left: 0;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  height: 44px;
  background: rgba(255, 255, 255, 0.85);
  border-top: 1px solid rgba(60, 60, 67, 0.08);
  border-bottom: 1px solid rgba(60, 60, 67, 0.08);
  pointer-events: none;
}

.ga-score-picker-scroll {
  height: 100%;
  overflow-y: auto;
  overflow-x: hidden;
  scroll-snap-type: y mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.ga-score-picker-scroll::-webkit-scrollbar {
  display: none;
}

.ga-score-picker-spacer {
  height: 88px;
  scroll-snap-align: center;
  flex-shrink: 0;
}

.ga-score-picker-item {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 44px;
  font-size: 20px;
  font-weight: 500;
  color: var(--ga-text-secondary);
  background: transparent;
  border: none;
  cursor: pointer;
  scroll-snap-align: center;
  flex-shrink: 0;
  transition: color 0.15s ease, font-weight 0.15s ease;
  -webkit-tap-highlight-color: transparent;
}

.ga-score-picker-item--selected {
  color: var(--ga-text);
  font-weight: 600;
}

.ga-score-picker-footer {
  padding: 8px 16px 16px;
  text-align: center;
}

.ga-score-picker-max {
  font-size: 13px;
  color: var(--ga-text-secondary);
}

/* Transition */
.ga-picker-fade-enter-active,
.ga-picker-fade-leave-active {
  transition: opacity 0.25s ease;
}

.ga-picker-fade-enter-active .ga-score-picker-sheet,
.ga-picker-fade-leave-active .ga-score-picker-sheet {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}

.ga-picker-fade-enter-from,
.ga-picker-fade-leave-to {
  opacity: 0;
}

.ga-picker-fade-enter-from .ga-score-picker-sheet,
.ga-picker-fade-leave-to .ga-score-picker-sheet {
  transform: translateY(100%);
}
</style>
