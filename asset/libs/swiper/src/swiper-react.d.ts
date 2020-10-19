import * as React from 'react';

import { SwiperOptions } from './types/swiper-options';
import SwiperClass from './types/swiper-class';

interface Swiper extends SwiperOptions {
  /**
   * Swiper container tag
   *
   * @default 'div'
   */
  tag?: string;

  /**
   * Swiper wrapper tag
   *
   * @default 'div'
   */
  wrapperTag?: string;

  /**
   * Get Swiper instance
   */
  onSwiper?: (swiper: SwiperClass) => void;

  // CORE_EVENTS
  // MODULES_EVENTS
}

interface SwiperSlide {
  /**
   * Slide tag
   *
   * @default 'div'
   */
  tag?: string;

  /**
   * Enables additional wrapper required for zoom mode
   *
   * @default false
   */
  zoom?: boolean;

  /**
   * Slide's index in slides array/collection
   *
   * @default false
   */
  virtualIndex?: number;
}

interface Swiper
  extends Omit<
    React.HTMLAttributes<HTMLElement>,
    'onProgress' | 'onClick' | 'onTouchEnd' | 'onTouchMove' | 'onTouchStart' | 'onTransitionEnd'
  > {}
interface SwiperSlide extends React.HTMLAttributes<HTMLElement> {}

declare const Swiper: React.FunctionComponent<Swiper>;
declare const SwiperSlide: React.FunctionComponent<SwiperSlide>;

export { Swiper, SwiperSlide };
