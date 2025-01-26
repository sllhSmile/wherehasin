<?php

namespace SllhSmile\WhereHasIn;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use SllhSmile\WhereHasIn\Builder\WhereHasIn;
use SllhSmile\WhereHasIn\Builder\WhereHasMorphIn;
use SllhSmile\WhereHasIn\Builder\WhereHasNotIn;
class ConfigProvider
{
    public function __invoke()
    {
        Builder::macro('whereHasIn', function ($relationName, $callable = null) {
            return (new WhereHasIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });
        Builder::macro('orWhereHasIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasIn($relationName, $callable);
            });
        });

        Builder::macro('whereHasNotIn', function ($relationName, $callable = null) {
            return (new WhereHasNotIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasNotIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });
        Builder::macro('orWhereHasNotIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasNotIn($relationName, $callable);
            });
        });

        Builder::macro('whereHasMorphIn', WhereHasMorphIn::make());
        Builder::macro('orWhereHasMorphIn', function ($relation, $types, $callback = null) {
            return $this->whereHasMorphIn($relation, $types, $callback, 'or');
        });
        return [];
    }
}
