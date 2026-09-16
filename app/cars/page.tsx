"use client";

import { useState, useMemo } from "react";
import { motion } from "framer-motion";
import { Search, SlidersHorizontal, X, ChevronDown } from "lucide-react";
import { CarCard } from "@/components/CarCard";
import { AnimatedPage, staggerContainer } from "@/components/AnimatedPage";
import { MOCK_CARS, filterCars } from "@/lib/mock-data";
import { cn, CATEGORY_LABELS } from "@/lib/utils";

const CATEGORIES = ["ALL", "SEDAN", "SUV", "SPORTS", "LUXURY", "ELECTRIC", "CONVERTIBLE"];
const PRICE_RANGES = [
  { label: "Any Price", min: 0, max: Infinity },
  { label: "Under $100/day", min: 0, max: 100 },
  { label: "$100–$250/day", min: 100, max: 250 },
  { label: "$250–$500/day", min: 250, max: 500 },
  { label: "$500+/day", min: 500, max: Infinity },
];

export default function BrowseCarsPage() {
  const [search, setSearch] = useState("");
  const [category, setCategory] = useState("ALL");
  const [priceRange, setPriceRange] = useState(0);
  const [transmission, setTransmission] = useState("");
  const [onlyAvailable, setOnlyAvailable] = useState(false);
  const [sortBy, setSortBy] = useState("featured");
  const [showFilters, setShowFilters] = useState(false);

  const selectedPrice = PRICE_RANGES[priceRange];

  const filteredCars = useMemo(() => {
    let cars = filterCars({
      search,
      category: category === "ALL" ? undefined : category,
      minPrice: selectedPrice.min,
      maxPrice: selectedPrice.max === Infinity ? undefined : selectedPrice.max,
      transmission: transmission || undefined,
      available: onlyAvailable ? true : undefined,
    });

    // Sort
    if (sortBy === "price-asc") cars = [...cars].sort((a, b) => a.pricePerDay - b.pricePerDay);
    if (sortBy === "price-desc") cars = [...cars].sort((a, b) => b.pricePerDay - a.pricePerDay);
    if (sortBy === "rating") cars = [...cars].sort((a, b) => b.rating - a.rating);
    if (sortBy === "featured") cars = [...cars].sort((a, b) => (b.isFeatured ? 1 : 0) - (a.isFeatured ? 1 : 0));

    return cars;
  }, [search, category, priceRange, transmission, onlyAvailable, sortBy]);

  const hasFilters = search || category !== "ALL" || priceRange !== 0 || transmission || onlyAvailable;

  function clearFilters() {
    setSearch("");
    setCategory("ALL");
    setPriceRange(0);
    setTransmission("");
    setOnlyAvailable(false);
  }

  return (
    <AnimatedPage className="min-h-screen pt-20">
      {/* ── Header ── */}
      <div className="bg-dark-900/60 border-b border-dark-800 py-10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="font-display text-4xl md:text-5xl font-bold text-white mb-2">
            Browse <span className="gradient-text">Our Fleet</span>
          </h1>
          <p className="text-dark-400">
            {MOCK_CARS.length} premium vehicles · Find your perfect drive
          </p>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* ── Search & Filter Bar ── */}
        <div className="flex flex-col md:flex-row gap-4 mb-6">
          {/* Search */}
          <div className="relative flex-1">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by make, model, or category..."
              className="input pl-12"
            />
          </div>

          {/* Sort */}
          <div className="relative">
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value)}
              className="input pr-10 appearance-none cursor-pointer md:w-48"
            >
              <option value="featured">Featured First</option>
              <option value="price-asc">Price: Low → High</option>
              <option value="price-desc">Price: High → Low</option>
              <option value="rating">Top Rated</option>
            </select>
            <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-400 pointer-events-none" />
          </div>

          {/* Filter Toggle */}
          <button
            onClick={() => setShowFilters(!showFilters)}
            className={cn(
              "btn-secondary gap-2 shrink-0",
              showFilters && "bg-brand-500/10 border-brand-500/40 text-brand-400"
            )}
          >
            <SlidersHorizontal className="w-4 h-4" />
            Filters
            {hasFilters && (
              <span className="w-2 h-2 rounded-full bg-brand-500" />
            )}
          </button>
        </div>

        {/* ── Expanded Filters ── */}
        {showFilters && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            className="card p-5 mb-6 overflow-hidden"
          >
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
              {/* Category */}
              <div>
                <label className="block text-xs font-semibold text-dark-300 uppercase tracking-wider mb-2">
                  Category
                </label>
                <div className="flex flex-wrap gap-2">
                  {CATEGORIES.map((cat) => (
                    <button
                      key={cat}
                      onClick={() => setCategory(cat)}
                      className={cn(
                        "px-3 py-1.5 rounded-lg text-xs font-medium transition-colors",
                        category === cat
                          ? "bg-brand-500 text-white"
                          : "bg-dark-700 text-dark-300 hover:text-white hover:bg-dark-600"
                      )}
                    >
                      {CATEGORY_LABELS[cat] || cat}
                    </button>
                  ))}
                </div>
              </div>

              {/* Price */}
              <div>
                <label className="block text-xs font-semibold text-dark-300 uppercase tracking-wider mb-2">
                  Price Range
                </label>
                <div className="space-y-1.5">
                  {PRICE_RANGES.map((range, i) => (
                    <button
                      key={i}
                      onClick={() => setPriceRange(i)}
                      className={cn(
                        "block w-full text-left px-3 py-1.5 rounded-lg text-xs transition-colors",
                        priceRange === i
                          ? "bg-brand-500/20 text-brand-300"
                          : "text-dark-400 hover:text-white hover:bg-dark-700"
                      )}
                    >
                      {range.label}
                    </button>
                  ))}
                </div>
              </div>

              {/* Transmission */}
              <div>
                <label className="block text-xs font-semibold text-dark-300 uppercase tracking-wider mb-2">
                  Transmission
                </label>
                <div className="space-y-1.5">
                  {["", "AUTOMATIC", "MANUAL"].map((t) => (
                    <button
                      key={t}
                      onClick={() => setTransmission(t)}
                      className={cn(
                        "block w-full text-left px-3 py-1.5 rounded-lg text-xs transition-colors",
                        transmission === t
                          ? "bg-brand-500/20 text-brand-300"
                          : "text-dark-400 hover:text-white hover:bg-dark-700"
                      )}
                    >
                      {t === "" ? "Any" : t.charAt(0) + t.slice(1).toLowerCase()}
                    </button>
                  ))}
                </div>
              </div>

              {/* Availability */}
              <div>
                <label className="block text-xs font-semibold text-dark-300 uppercase tracking-wider mb-2">
                  Availability
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <div
                    onClick={() => setOnlyAvailable(!onlyAvailable)}
                    className={cn(
                      "w-10 h-5 rounded-full transition-colors relative cursor-pointer",
                      onlyAvailable ? "bg-brand-500" : "bg-dark-600"
                    )}
                  >
                    <span className={cn("absolute top-0.5 w-4 h-4 bg-white rounded-full transition-transform shadow", onlyAvailable ? "translate-x-5" : "translate-x-0.5")} />
                  </div>
                  <span className="text-sm text-dark-300">Available only</span>
                </label>
              </div>
            </div>

            {hasFilters && (
              <button onClick={clearFilters} className="mt-4 flex items-center gap-1.5 text-sm text-dark-400 hover:text-brand-400 transition-colors">
                <X className="w-4 h-4" /> Clear all filters
              </button>
            )}
          </motion.div>
        )}

        {/* ── Category Pills (quick filter) ── */}
        <div className="flex gap-2 overflow-x-auto no-scrollbar mb-8 pb-1">
          {CATEGORIES.map((cat) => (
            <button
              key={cat}
              onClick={() => setCategory(cat)}
              className={cn(
                "shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200",
                category === cat
                  ? "bg-brand-500 text-white shadow-lg shadow-brand-500/25"
                  : "bg-dark-800 text-dark-300 hover:text-white hover:bg-dark-700 border border-dark-700"
              )}
            >
              {CATEGORY_LABELS[cat] || cat}
            </button>
          ))}
        </div>

        {/* ── Results Count ── */}
        <div className="flex items-center justify-between mb-6">
          <p className="text-dark-400 text-sm">
            <span className="text-white font-semibold">{filteredCars.length}</span> car{filteredCars.length !== 1 ? "s" : ""} found
          </p>
          {hasFilters && (
            <button onClick={clearFilters} className="text-sm text-brand-400 hover:text-brand-300 transition-colors">
              Clear filters
            </button>
          )}
        </div>

        {/* ── Car Grid ── */}
        {filteredCars.length > 0 ? (
          <motion.div
            key={`${category}-${search}-${priceRange}-${transmission}-${onlyAvailable}`}
            variants={staggerContainer}
            initial="initial"
            animate="animate"
            className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
          >
            {filteredCars.map((car, i) => (
              <CarCard key={car.id} car={car} index={i} />
            ))}
          </motion.div>
        ) : (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            className="text-center py-20"
          >
            <div className="text-6xl mb-4">🚗</div>
            <h3 className="text-xl font-semibold text-white mb-2">No cars found</h3>
            <p className="text-dark-400 mb-6">Try adjusting your filters or search query</p>
            <button onClick={clearFilters} className="btn-primary">
              Clear Filters
            </button>
          </motion.div>
        )}
      </div>
    </AnimatedPage>
  );
}
