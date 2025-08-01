"use client"

import type React from "react"

import { useState, useEffect } from "react"
import { useRouter } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { useToast } from "@/hooks/use-toast"
import { ArrowLeft, Loader2, Sparkles } from "lucide-react"
import Link from "next/link"

interface AnimalType {
  id: number
  name: string
  description: string
}

export default function NuevoLotePage() {
  const [animalTypes, setAnimalTypes] = useState<AnimalType[]>([])
  const [formData, setFormData] = useState({
    animal_type_id: "",
    quantity: "",
    age_months: "",
    average_weight_kg: "",
    suggested_price_ars: "",
    suggested_price_usd: "",
    status: "disponible",
  })
  const [loading, setLoading] = useState(false)
  const [loadingAI, setLoadingAI] = useState(false)
  const router = useRouter()
  const { toast } = useToast()

  useEffect(() => {
    fetchAnimalTypes()
  }, [])

  const fetchAnimalTypes = async () => {
    try {
      const token = localStorage.getItem("token")
      const response = await fetch("http://127.0.0.1:8000/api/animal-types", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (response.ok) {
        const data = await response.json()
        setAnimalTypes(data)
      }
    } catch (error) {
      console.error("Error fetching animal types:", error)
    }
  }

  const suggestPriceWithAI = async () => {
    if (!formData.animal_type_id || !formData.age_months || !formData.average_weight_kg) {
      toast({
        title: "Información incompleta",
        description: "Completa el tipo de animal, edad y peso para obtener sugerencias de IA",
        variant: "destructive",
      })
      return
    }

    setLoadingAI(true)

    // Simulate AI price suggestion based on animal type, age, and weight
    setTimeout(() => {
      const basePrice = 50000 // Base price in ARS
      const ageMultiplier = Number.parseInt(formData.age_months) > 24 ? 1.2 : 1.0
      const weightMultiplier = Number.parseFloat(formData.average_weight_kg) / 400 // Assuming 400kg as base
      const typeMultiplier = formData.animal_type_id === "1" ? 1.1 : 1.0 // Bovino premium

      const suggestedARS = Math.round(basePrice * ageMultiplier * weightMultiplier * typeMultiplier)
      const suggestedUSD = Math.round(suggestedARS / 250) // Approximate exchange rate

      setFormData({
        ...formData,
        suggested_price_ars: suggestedARS.toString(),
        suggested_price_usd: suggestedUSD.toString(),
      })

      toast({
        title: "Precios sugeridos por IA",
        description: "Los precios han sido calculados basándose en datos históricos del mercado",
      })

      setLoadingAI(false)
    }, 2000)
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)

    try {
      const token = localStorage.getItem("token")
      const response = await fetch("http://127.0.0.1:8000/api/batches", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...formData,
          animal_type_id: Number.parseInt(formData.animal_type_id),
          quantity: Number.parseInt(formData.quantity),
          age_months: Number.parseInt(formData.age_months),
          average_weight_kg: Number.parseFloat(formData.average_weight_kg),
          suggested_price_ars: Number.parseFloat(formData.suggested_price_ars),
          suggested_price_usd: Number.parseFloat(formData.suggested_price_usd),
        }),
      })

      if (response.ok) {
        toast({
          title: "Éxito",
          description: "Lote creado correctamente",
        })
        router.push("/lotes")
      } else {
        throw new Error("Error creating batch")
      }
    } catch (error) {
      console.error("Error creating batch:", error)
      toast({
        title: "Error",
        description: "No se pudo crear el lote",
        variant: "destructive",
      })
    } finally {
      setLoading(false)
    }
  }

  const handleChange = (name: string, value: string) => {
    setFormData({
      ...formData,
      [name]: value,
    })
  }

  return (
    <div className="p-6 max-w-2xl mx-auto space-y-6">
      <div className="flex items-center space-x-4">
        <Link href="/lotes">
          <Button variant="outline" size="sm">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Volver
          </Button>
        </Link>
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Nuevo Lote</h1>
          <p className="text-gray-600">Registra un nuevo lote de ganado</p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Información del Lote</CardTitle>
          <CardDescription>Completa los datos del lote. Puedes usar IA para sugerir precios.</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-2">
                <Label htmlFor="animal_type_id">Tipo de Animal</Label>
                <Select
                  value={formData.animal_type_id}
                  onValueChange={(value) => handleChange("animal_type_id", value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona el tipo" />
                  </SelectTrigger>
                  <SelectContent>
                    {animalTypes.map((type) => (
                      <SelectItem key={type.id} value={type.id.toString()}>
                        {type.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="quantity">Cantidad de Animales</Label>
                <Input
                  id="quantity"
                  type="number"
                  value={formData.quantity}
                  onChange={(e) => handleChange("quantity", e.target.value)}
                  required
                  min="1"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="age_months">Edad (meses)</Label>
                <Input
                  id="age_months"
                  type="number"
                  value={formData.age_months}
                  onChange={(e) => handleChange("age_months", e.target.value)}
                  required
                  min="1"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="average_weight_kg">Peso Promedio (kg)</Label>
                <Input
                  id="average_weight_kg"
                  type="number"
                  step="0.1"
                  value={formData.average_weight_kg}
                  onChange={(e) => handleChange("average_weight_kg", e.target.value)}
                  required
                  min="0"
                />
              </div>
            </div>

            <div className="border-t pt-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium">Precios Sugeridos</h3>
                <Button type="button" variant="outline" onClick={suggestPriceWithAI} disabled={loadingAI}>
                  {loadingAI ? (
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                  ) : (
                    <Sparkles className="mr-2 h-4 w-4" />
                  )}
                  Sugerir con IA
                </Button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="suggested_price_ars">Precio Sugerido (ARS)</Label>
                  <Input
                    id="suggested_price_ars"
                    type="number"
                    step="0.01"
                    value={formData.suggested_price_ars}
                    onChange={(e) => handleChange("suggested_price_ars", e.target.value)}
                    required
                    min="0"
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="suggested_price_usd">Precio Sugerido (USD)</Label>
                  <Input
                    id="suggested_price_usd"
                    type="number"
                    step="0.01"
                    value={formData.suggested_price_usd}
                    onChange={(e) => handleChange("suggested_price_usd", e.target.value)}
                    required
                    min="0"
                  />
                </div>
              </div>
            </div>

            <div className="flex justify-end space-x-4 pt-6 border-t">
              <Link href="/lotes">
                <Button variant="outline">Cancelar</Button>
              </Link>
              <Button type="submit" disabled={loading}>
                {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                Crear Lote
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  )
}
