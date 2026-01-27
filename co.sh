#!/bin/bash

# Configuración
FECHA_INICIAL="2026-01-25 08:00:00"
MIN_SEGUNDOS=30
MAX_SEGUNDOS=90

echo "🔍 Obteniendo commits..."
rm -f /tmp/git_dates.map

commits=($(git rev-list --all --reverse))
total=${#commits[@]}

echo "📊 Total de commits: $total"
echo "⏱️  Cada commit tendrá entre $MIN_SEGUNDOS-$MAX_SEGUNDOS segundos de diferencia"
echo ""

fecha_base=$(python -c "from datetime import datetime; print(int(datetime.strptime('$FECHA_INICIAL', '%Y-%m-%d %H:%M:%S').timestamp()))")

echo "📅 Creando mapeo de fechas..."

segundos_acumulados=0

for i in "${!commits[@]}"; do
    # Generar segundos aleatorios entre MIN y MAX
    segundos_random=$((RANDOM % (MAX_SEGUNDOS - MIN_SEGUNDOS + 1) + MIN_SEGUNDOS))
    segundos_acumulados=$((segundos_acumulados + segundos_random))
    
    nuevo_timestamp=$((fecha_base + segundos_acumulados))
    
    nueva_fecha=$(python -c "from datetime import datetime; print(datetime.fromtimestamp($nuevo_timestamp).strftime('%Y-%m-%d %H:%M:%S'))")
    
    echo "${commits[$i]}|$nueva_fecha" >> /tmp/git_dates.map
    
    if (( i % 20 == 0 )) || (( i == total - 1 )); then
        echo "Commit $i -> $nueva_fecha (+${segundos_random}s)"
    fi
done

echo ""
echo "✅ Mapeo creado"
read -p "⚠️  ¿Ejecutar filter-branch? (yes/no): " respuesta

if [ "$respuesta" != "yes" ]; then
    echo "❌ Cancelado"
    exit 0
fi

echo ""
echo "🔄 Ejecutando filter-branch..."

git filter-branch --force --env-filter '
while IFS="|" read -r commit fecha; do
    if [ "$GIT_COMMIT" = "$commit" ]; then
        export GIT_AUTHOR_DATE="$fecha"
        export GIT_COMMITTER_DATE="$fecha"
        break
    fi
done < /tmp/git_dates.map
' --tag-name-filter cat -- --all

if [ $? -eq 0 ]; then
    echo "✅ Completado"
    echo "git log --pretty=format:'%h %ai %s' | head -20"
fi

rm -f /tmp/git_dates.map
```

**Ejemplos de resultado:**

Con `SEGUNDOS_POR_COMMIT=45`:
```
Commit 0  -> 2026-01-25 08:00:00
Commit 1  -> 2026-01-25 08:00:45
Commit 2  -> 2026-01-25 08:01:30
Commit 3  -> 2026-01-25 08:02:15
...
Commit 20 -> 2026-01-25 08:15:00
```

Con segundos aleatorios (30-90):
```
Commit 0  -> 2026-01-25 08:00:00
Commit 1  -> 2026-01-25 08:00:52 (+52s)
Commit 2  -> 2026-01-25 08:02:09 (+77s)
Commit 3  -> 2026-01-25 08:03:42 (+93s)
