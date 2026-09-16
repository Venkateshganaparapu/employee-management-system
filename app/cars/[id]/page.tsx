"use client";

import { useState } from "react";
import { notFound, useParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import {
  Star,
  Users,
  Fuel,
  Settings,
  Zap,
  MapPin,
  ArrowLeft,
  Check,
  ChevronLeft,
  ChevronRight,
  Share2,
  Heart,
} from "lucide-react";
import { getCarById } from "@/lib/mock-data";
import { cn, formatCurrency, CATEGORY_COLORS, CATEGORY_LABELS } from "@/lib/utils";
import { AnimatedPage } from "@/components/AnimatedPage";

export default function CarDetailPage() {
  const params = useParams();
  const car = getCarById(params.id as string);

  const [imageIndex, setImageIndex] = useState(0);
  const [wishlist, setWishlist] = useState(false);

  if (!car) return notFound();

  const prevImage = () => setImageIndex((i) => (i - 1 + car.images.length) % car.images.length);
  const nextImage = () => setImageIndex((i) => (i + 1) % car.images.length);

  return (
    <AnimatedPage className="min-h-screen pt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Back */}
        <Link
          href="/cars"
          className="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors mb-6"
        >
          <ArrowLeft className="w-4 h-4" />
          Back to Fleet
        </Link>

        <div className="grid grid-cols-1 lg:grid-cols-5 gap-10">
          {/* ── Left: Images + Details ── */}
          <div className="lg:col-span-3 space-y-6">
            {/* Image Gallery */}
            <div className="relative rounded-2xl overflow-hidden bg-dark-800 aspect-[16/10]">
              <AnimatePresence mode="wait">
                <motion.div
                  key={imageIndex}
                  initial={{ opacity: 0, scale: 1.02 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.98 }}
                  transition={{ duration: 0.3 }}
                  className="absolute inset-0"
                >
                  <Image
                    src={car.images[imageIndex]}
                    alt={`${car.make} ${car.model} image ${imageIndex + 1}`}
                    fill
                    className="object-cover"
                    priority
                  />
                </motion.div>
              </AnimatePresence>

              {/* Nav arrows */}
              {car.images.length > 1 && (
                <>
                  <button
                    onClick={prevImage}
                    className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full glass flex items-center justify-center text-white hover:bg-white/20 transition-colors"
                  >
                    <ChevronLeft className="w-5 h-5" />
                  </button>
                  <button
                    onClick={nextImage}
                    className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full glass flex items-center justify-center text-white hover:bg-white/20 transition-colors"
                  >
                    <ChevronRight className="w-5 h-5" />
                  </button>
                </>
              )}

              {/* Indicator dots */}
              <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                {car.images.map((_, i) => (
                  <button
                    key={i}
                    onClick={() => setImageIndex(i)}
                    className={cn(
                      "h-1.5 rounded-full transition-all",
                      i === imageIndex ? "w-6 bg-white" : "w-1.5 bg-white/40 hover:bg-white/60"
                    )}
                  />
                ))}
              </div>

              {/* Badges */}
              <div className="absolute top-4 left-4">
                <span className={cn("badge border", CATEGORY_COLORS[car.category])}>
                  {CATEGORY_LABELS[car.category]}
                </span>
              </div>
            </div>

            {/* Thumbnails */}
            <div className="flex gap-3 overflow-x-auto no-scrollbar">
              {car.images.map((img, i) => (
                <button
                  key={i}
                  onClick={() => setImageIndex(i)}
                  className={cn(
                    "shrink-0 w-24 h-16 rounded-xl overflow-hidden border-2 transition-colors",
                    i === imageIndex ? "border-brand-500" : "border-dark-600 hover:border-dark-500"
                  )}
                >
                  <Image src={img} alt={`thumb ${i}`} width={96} height={64} className="object-cover w-full h-full" />
                </button>
              ))}
            </div>

            {/* Description */}
            <div className="card p-6">
              <h2 className="font-display text-xl font-bold text-white mb-3">About This Car</h2>
              <p className="text-dark-300 leading-relaxed">{car.description}</p>
            </div>

            {/* Features */}
            <div className="card p-6">
              <h2 className="font-display text-xl font-bold text-white mb-4">Features & Amenities</h2>
              <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                {car.features.map((feature) => (
                  <div
                    key={feature}
                    className="flex items-center gap-2.5 p-3 bg-dark-700 rounded-xl"
                  >
                    <Check className="w-4 h-4 text-brand-400 shrink-0" />
                    <span className="text-sm text-dark-200">{feature}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* ── Right: Booking Panel ── */}
          <div className="lg:col-span-2">
            <div className="sticky top-24 space-y-5">
              {/* Header */}
              <div className="card p-6">
                <div className="flex items-start justify-between mb-4">
                  <div>
                    <h1 className="font-display text-2xl font-bold text-white">
                      {car.make} {car.model}
                    </h1>
                    <p className="text-dark-400 mt-1">{car.year} · {car.color}</p>
                  </div>
                  <div className="flex gap-2">
                    <button
                      onClick={() => setWishlist(!wishlist)}
                      className={cn(
                        "p-2.5 rounded-xl transition-colors border",
                        wishlist
                          ? "bg-red-500/10 border-red-500/30 text-red-400"
                          : "bg-dark-700 border-dark-600 text-dark-400 hover:text-white"
                      )}
                    >
                      <Heart className={cn("w-5 h-5", wishlist && "fill-red-400")} />
                    </button>
                    <button className="p-2.5 rounded-xl bg-dark-700 border border-dark-600 text-dark-400 hover:text-white transition-colors">
                      <Share2 className="w-5 h-5" />
                    </button>
                  </div>
                </div>

                {/* Rating */}
                <div className="flex items-center gap-2 mb-5">
                  <div className="flex gap-0.5">
                    {Array.from({ length: 5 }).map((_, i) => (
                      <Star
                        key={i}
                        className={cn("w-4 h-4", i < Math.floor(car.rating) ? "text-yellow-400 fill-yellow-400" : "text-dark-600")}
                      />
                    ))}
                  </div>
                  <span className="text-white font-semibold">{car.rating}</span>
                  <span className="text-dark-400 text-sm">({car.reviewCount} reviews)</span>
                </div>

                {/* Specs grid */}
                <div className="grid grid-cols-2 gap-3 mb-5">
                  {[
                    { icon: Users, label: "Seats", value: `${car.seats} persons` },
                    { icon: Settings, label: "Gearbox", value: car.transmission },
                    { icon: car.fuelType === "Electric" ? Zap : Fuel, label: "Fuel", value: car.fuelType },
                    { icon: MapPin, label: "Location", value: car.location },
                  ].map((spec) => (
                    <div key={spec.label} className="bg-dark-700 rounded-xl p-3">
                      <spec.icon className="w-4 h-4 text-brand-400 mb-1.5" />
                      <p className="text-xs text-dark-400">{spec.label}</p>
                      <p className="text-sm font-medium text-white">{spec.value}</p>
                    </div>
                  ))}
                </div>

                {/* Price */}
                <div className="border-t border-dark-700 pt-5">
                  <div className="flex items-baseline gap-2 mb-1">
                    <span className="font-display text-3xl font-bold text-white">
                      {formatCurrency(car.pricePerDay)}
                    </span>
                    <span className="text-dark-400">/ day</span>
                  </div>
                  <p className="text-xs text-dark-500 mb-5">Taxes & fees included · Free cancellation</p>

                  {car.isAvailable ? (
                    <Link
                      href={`/booking/${car.id}`}
                      className="btn-primary w-full justify-center text-base py-3.5"
                    >
                      Book This Car
                    </Link>
                  ) : (
                    <button disabled className="w-full py-3.5 rounded-xl bg-dark-700 text-dark-400 font-semibold cursor-not-allowed">
                      Currently Unavailable
                    </button>
                  )}

                  <p className="text-center text-xs text-dark-500 mt-3">
                    You won&apos;t be charged until confirmed
                  </p>
                </div>
              </div>

              {/* Availability Badge */}
              <div className={cn(
                "flex items-center gap-3 p-4 rounded-xl border",
                car.isAvailable
                  ? "bg-green-500/5 border-green-500/20"
                  : "bg-red-500/5 border-red-500/20"
              )}>
                <div className={cn("w-3 h-3 rounded-full", car.isAvailable ? "bg-green-400 animate-pulse" : "bg-red-400")} />
                <div>
                  <p className={cn("text-sm font-semibold", car.isAvailable ? "text-green-300" : "text-red-300")}>
                    {car.isAvailable ? "Available Now" : "Currently Booked"}
                  </p>
                  <p className="text-xs text-dark-400">
                    {car.isAvailable ? "Pick-up available today" : "Check back later"}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AnimatedPage>
  );
}
